<?php

namespace App\Http\Controllers;

use App\Services\YandexReviewsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingController extends Controller
{
    public function __construct(private YandexReviewsService $yandex) {}

    // ─── GET /settings ────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => $request->user()->companySetting,
        ]);
    }

    // ─── POST /settings ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'yandex_url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    $host = parse_url($value, PHP_URL_HOST) ?? '';
                    if (!preg_match('/(\.|^)yandex\.(ru|com|by|kz|ua)$/i', $host)) {
                        $fail('URL должен быть ссылкой на Яндекс Карты.');
                    }
                },
            ],
        ]);

        $request->user()->companySetting()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'yandex_url'           => $data['yandex_url'],
                'last_synced_at'       => null,  // force re-sync on next load
                'yandex_rating'        => null,
                'yandex_total_reviews' => null,
            ]
        );

        return redirect('/reviews')->with('success', 'Настройки сохранены. Отзывы загружаются…');
    }

    // ─── GET /reviews  (Inertia page) ─────────────────────────────────────────
    public function reviewsPage(Request $request): Response
    {
        return Inertia::render('Reviews', [
            'settings' => $request->user()->companySetting,
            'hasApify' => !empty(config('services.apify.token')),
        ]);
    }

    // ─── GET /reviews/data  (JSON for axios) ──────────────────────────────────
    public function reviewsData(Request $request): JsonResponse
    {
        $request->validate([
            'page'     => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
            'sort'     => ['sometimes', 'string', 'in:date,rating_desc,rating_asc'],
        ]);

        $settings = $request->user()->companySetting;

        if (!$settings?->yandex_url) {
            return response()->json([
                'error' => 'Яндекс URL не настроен. Перейдите в раздел Настройки.',
            ], 422);
        }

        $result = $this->yandex->getReviews(
            $settings,
            (int)    $request->input('page',     1),
            (int)    $request->input('per_page', 10),
            (string) $request->input('sort',     'date'),
        );

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 502);
        }

        return response()->json($result);
    }

    // ─── POST /reviews/sync  (manual refresh button) ─────────────────────────
    public function syncReviews(Request $request): JsonResponse
    {
        $settings = $request->user()->companySetting;

        if (!$settings?->yandex_url) {
            return response()->json(['error' => 'Яндекс URL не настроен.'], 422);
        }

        $result = $this->yandex->sync($settings);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 502);
        }

        return response()->json([
            'message' => 'Синхронизация завершена.',
            'stats'   => $result['stats'],
        ]);
    }
}