<?php

namespace App\Services;

use App\Models\Review;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class YandexReviewsService
{
    private const APIFY_BASE      = 'https://api.apify.com/v2';
    private const MAX_REVIEWS     = 200;
    private const RUN_TIMEOUT_SEC = 600;
    private const POLL_INTERVAL   = 5;

    private string $token;
    private string $actorId;

    public function __construct()
    {
        $this->token   = config('services.apify.token', '');
        $this->actorId = str_replace('/', '~', config('services.apify.actor_id', 'zen-studio~yandex-maps-scraper'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public — get paginated reviews from DB
    // ─────────────────────────────────────────────────────────────────────────
    public function getReviews(
        CompanySetting $settings,
        int    $page    = 1,
        int    $perPage = 10,
        string $sort    = 'date'
    ): array {
        $this->ensureFreshData($settings);

        $query = Review::where('company_setting_id', $settings->id);

        match ($sort) {
            'rating_desc' => $query->orderByDesc('rating'),
            'rating_asc'  => $query->orderBy('rating'),
            default       => $query->orderByDesc('reviewed_at'),
        };

        $total      = $query->count();
        $reviewRows = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        $fresh      = $settings->fresh();

        return [
            'rating'        => (float) ($fresh->yandex_rating ?? 0),
            'total_reviews' => (int)   ($fresh->yandex_total_reviews ?? $total),
            'last_synced'   => $fresh->last_synced_at?->toIso8601String(),
            'reviews'       => $reviewRows->map(fn($r) => [
                'id'     => $r->id,
                'author' => $r->author,
                'rating' => $r->rating,
                'text'   => $r->text,
                'date'   => $r->reviewed_at?->toIso8601String(),
                'likes'  => $r->likes,
                'photos' => $r->photos ?? [],
            ])->toArray(),
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public — force full sync via Apify
    // ─────────────────────────────────────────────────────────────────────────
    public function sync(CompanySetting $settings): array
    {
        if (!$this->token) {
            return ['error' => 'APIFY_API_TOKEN не задан в .env'];
        }
        if (!$settings->yandex_url) {
            return ['error' => 'Yandex URL не настроен'];
        }

        Log::info('YandexReviewsService: starting sync', [
            'setting_id' => $settings->id,
            'actor'      => $this->actorId,
        ]);

        $run = $this->startRun($settings->yandex_url);
        if (isset($run['error'])) return $run;

        $poll = $this->pollUntilDone($run['run_id']);
        if (isset($poll['error'])) return $poll;

        $items = $this->fetchDataset($run['dataset_id']);
        if (isset($items['error'])) return $items;

        $stats = $this->persistReviews($settings, $items);

        Cache::forget("yandex_meta_{$settings->id}");
        $settings->update(['last_synced_at' => now()]);

        Log::info('YandexReviewsService: sync complete', $stats);
        return ['ok' => true, 'stats' => $stats];
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function ensureFreshData(CompanySetting $settings): void
    {
        $hasData = Review::where('company_setting_id', $settings->id)->exists();
        if (!$hasData) {
            $this->sync($settings);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 1 — Start Apify actor run
    // ─────────────────────────────────────────────────────────────────────────

    private function startRun(string $yandexUrl): array
    {
        Log::info('Apify Test: Checking Token...', ['token_exists' => !empty($this->token)]);

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->post(self::APIFY_BASE . "/acts/{$this->actorId}/runs", [
                    'startUrls'          => [['url' => $yandexUrl]],
                    'maxResults'         => 1,
                    'maxReviews'         => self::MAX_REVIEWS,
                    'enrichBusinessData' => true,
                    'language'           => 'ru',
                    'proxyConfig'        => ['useApifyProxy' => true],
                ]);

            // اختبار حالة الرد
            if ($response->status() === 401) {
                Log::error('Apify Test Error: Unauthorized. Your API Token is invalid.');
                return ['error' => 'Invalid API Token. Check your .env file.'];
            }

            if (!$response->successful()) {
                Log::error('Apify Test Error: Request Failed', [
                    'status' => $response->status(),
                    'body'   => $response->json()
                ]);
                return ['error' => "Apify Error: " . ($response->json('error.message') ?? 'Unknown error')];
            }

            $data = $response->json('data');
            Log::info('Apify Test Success: Run started', ['run_id' => $data['id']]);

            return ['run_id' => $data['id'], 'dataset_id' => $data['defaultDatasetId']];
        } catch (\Throwable $e) {
            Log::error('Apify Test Exception', ['message' => $e->getMessage()]);
            return ['error' => 'Connection Error: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 2 — Poll run status until SUCCEEDED
    // ─────────────────────────────────────────────────────────────────────────
    private function pollUntilDone(string $runId): array
    {
        $deadline = time() + self::RUN_TIMEOUT_SEC;

        while (time() < $deadline) {
            sleep(self::POLL_INTERVAL);

            try {
                $status = Http::timeout(15)
                    ->withToken($this->token)
                    ->get(self::APIFY_BASE . "/actor-runs/{$runId}")
                    ->json('data.status');

                Log::debug('Apify poll', ['run_id' => $runId, 'status' => $status]);

                if ($status === 'SUCCEEDED') return ['ok' => true];

                if (in_array($status, ['FAILED', 'ABORTED', 'TIMED-OUT'])) {
                    return ['error' => "Apify run ended with status: {$status}"];
                }
            } catch (\Throwable $e) {
                Log::warning('Apify poll exception', ['msg' => $e->getMessage()]);
            }
        }

        return ['error' => 'Timeout: run took longer than ' . self::RUN_TIMEOUT_SEC . 's'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Step 3 — Fetch dataset items
    // ─────────────────────────────────────────────────────────────────────────

    private function fetchDataset(string $datasetId): array
    {
        Log::info('Apify Test: Fetching Dataset...', ['dataset_id' => $datasetId]);

        try {
            $response = Http::timeout(60)
                ->withToken($this->token)
                ->get(self::APIFY_BASE . "/datasets/{$datasetId}/items");

            $items = $response->json();

            if (empty($items)) {
                Log::warning('Apify Test Warning: Dataset is empty. Maybe Yandex blocked the scraper or URL is wrong.');
                return ['error' => 'No reviews found on this URL.'];
            }

            Log::info('Apify Test: Data Received', ['count' => count($items), 'first_item_sample' => array_keys($items[0] ?? [])]);

            return $items;
        } catch (\Throwable $e) {
            Log::error('Apify Test: Dataset Fetch Exception', ['msg' => $e->getMessage()]);
            return ['error' => 'Failed to fetch data: ' . $e->getMessage()];
        }
    }
    // ─────────────────────────────────────────────────────────────────────────
    // Step 4 — Persist reviews to DB
    // ─────────────────────────────────────────────────────────────────────────
    private function persistReviews(CompanySetting $settings, array $items): array
    {
        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        // تحديث التقييم العام من أول عنصر يحتوي على البيانات
        if (!empty($items[0])) {
            $this->updateMeta($settings, $items[0]);
        }

        foreach ($items as $item) {
            $normalized = $this->normalizeReview($item);

            if (!$normalized['external_id']) {
                $skipped++;
                continue;
            }

            $payload = array_merge($normalized, [
                'company_setting_id' => $settings->id,
            ]);

            $exists = Review::where('company_setting_id', $settings->id)
                ->where('external_id', $normalized['external_id'])
                ->exists();

            if ($exists) {
                Review::where('company_setting_id', $settings->id)
                    ->where('external_id', $normalized['external_id'])
                    ->update($payload);
                $updated++;
            } else {
                Review::create($payload);
                $inserted++;
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'total'    => $inserted + $updated,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Normalize one review
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeReview(array $r): array
    {
        $externalId = (string) ($r['reviewId'] ?? $r['id'] ?? '');
        $isAnonymous = (bool) ($r['isAnonymous'] ?? false);

        if ($isAnonymous) {
            $author = 'Аноним';
        } else {
            $raw = $r['authorName'] ?? $r['reviewerName'] ?? $r['author']['name'] ?? $r['author'] ?? '';
            $author = trim((string) (is_array($raw) ? ($raw['name'] ?? '') : $raw));
            if (empty($author)) $author = 'Аноним';
        }

        $rating = (int) ($r['rating'] ?? $r['stars'] ?? 0);
        $text = (string) ($r['text'] ?? $r['reviewText'] ?? $r['comment'] ?? '');
        $dateRaw = $r['date'] ?? $r['publishedAtDate'] ?? $r['createdAt'] ?? $r['updatedTime'] ?? null;
        $likes = (int) ($r['likeCount'] ?? $r['likesCount'] ?? $r['likes'] ?? 0);

        $photos = $r['photos'] ?? $r['reviewImageUrls'] ?? [];
        if (!is_array($photos)) $photos = [];
        $photos = array_values(array_filter($photos, fn($p) => is_string($p)));

        return [
            'external_id' => $externalId ?: null,
            'author'      => $author,
            'rating'      => max(0, min(5, $rating)),
            'text'        => $text,
            'reviewed_at' => $this->parseDate($dateRaw),
            'likes'       => $likes,
            'photos'      => $photos,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // تحديث التقييم العام وعدد المراجعات في جدول CompanySetting
    // ─────────────────────────────────────────────────────────────────────────
    private function updateMeta(CompanySetting $settings, array $item): void
    {
        $update = [];

        // محاولة جلب التقييم من عدة حقول محتملة (بناءً على تفعيل Enrichment)
        $rating = $item['businessRating'] ?? $item['rating'] ?? null;
        if ($rating !== null) {
            $update['yandex_rating'] = (float) $rating;
        }

        // محاولة جلب عدد المراجعات الكلي
        $count = $item['businessRatingsCount'] ?? $item['reviewCount'] ?? $item['ratingsCount'] ?? null;
        if ($count !== null) {
            $update['yandex_total_reviews'] = (int) $count;
        }

        if (!empty($update)) {
            $settings->update($update);
            Log::info('Yandex Meta Updated Successfully', $update);
        } else {
            Log::warning('Yandex Meta NOT found in Apify item keys: ' . implode(', ', array_keys($item)));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function parseDate(mixed $dateStr): ?Carbon
    {
        if (!$dateStr) return null;
        if ($dateStr instanceof Carbon) return $dateStr;
        try {
            return Carbon::parse((string) $dateStr);
        } catch (\Throwable) {
            return null;
        }
    }
}
