<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Services\YandexReviewsService;
use Illuminate\Console\Command;

class SyncYandexReviews extends Command
{
    protected $signature   = 'yandex:sync-reviews
                                {--setting= : Sync a specific company_setting ID only}
                                {--force    : Sync even if data is fresh}';

    protected $description = 'Sync Yandex Maps reviews via Apify for all configured companies';

    public function handle(YandexReviewsService $service): int
    {
        $settingId = $this->option('setting');
        $force     = $this->option('force');

        $query = CompanySetting::whereNotNull('yandex_url');

        if ($settingId) {
            $query->where('id', $settingId);
        }

        $settings = $query->get();

        if ($settings->isEmpty()) {
            $this->warn('No company settings with a Yandex URL found.');
            return self::SUCCESS;
        }

        $this->info("Starting sync for {$settings->count()} company(-ies)…");
        $bar = $this->output->createProgressBar($settings->count());
        $bar->start();

        $ok    = 0;
        $fail  = 0;
        $skip  = 0;

        foreach ($settings as $setting) {
            // Skip if fresh and not forced
            if (!$force && !$setting->isStale()) {
                $this->line("\n  ⏭  Setting #{$setting->id} is fresh — skipping");
                $skip++;
                $bar->advance();
                continue;
            }

            $result = $service->sync($setting);

            if (isset($result['error'])) {
                $this->newLine();
                $this->error("  ✗ Setting #{$setting->id}: {$result['error']}");
                $fail++;
            } else {
                $stats = $result['stats'];
                $this->newLine();
                $this->info(
                    "  ✓ Setting #{$setting->id}: "
                    . "{$stats['inserted']} new, {$stats['updated']} updated, "
                    . "{$stats['skipped']} skipped"
                );
                $ok++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(
            ['✓ Synced', '✗ Failed', '⏭ Skipped'],
            [[$ok, $fail, $skip]]
        );

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}