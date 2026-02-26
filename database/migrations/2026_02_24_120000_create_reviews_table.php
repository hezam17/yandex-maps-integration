<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // FK to the company's settings row
            $table->foreignId('company_setting_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Original ID from Apify / Yandex
            $table->string('external_id');

            $table->string('author')->default('Аноним');
            $table->unsignedTinyInteger('rating')->default(0);  // 1–5
            $table->text('text')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->json('photos')->nullable();   // array of image URLs

            $table->timestamps();

            // One review per external_id per company
            $table->unique(['company_setting_id', 'external_id']);
            $table->index(['company_setting_id', 'reviewed_at']);
            $table->index(['company_setting_id', 'rating']);
        });

        // Add new columns to company_settings
        Schema::table('company_settings', function (Blueprint $table) {
            $table->float('yandex_rating')->nullable()->after('yandex_business_id');
            $table->unsignedInteger('yandex_total_reviews')->nullable()->after('yandex_rating');
            $table->timestamp('last_synced_at')->nullable()->after('yandex_total_reviews');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['yandex_rating', 'yandex_total_reviews', 'last_synced_at']);
        });
    }
};