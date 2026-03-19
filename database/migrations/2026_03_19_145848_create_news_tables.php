<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamp('featured_until')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('news_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['news_id', 'locale'], 'news_trn_news_locale_uq');
            $table->unique(['locale', 'slug'], 'news_trn_locale_slug_uq');
        });

        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('news_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();

            $table->unique(['news_category_id', 'locale'], 'news_cat_trn_cat_locale_uq');
        });

        Schema::create('news_news_category', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained()->cascadeOnDelete();
            $table->foreignId('news_category_id')->constrained()->cascadeOnDelete();

            $table->primary(['news_id', 'news_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_news_category');
        Schema::dropIfExists('news_category_translations');
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('news_translations');
        Schema::dropIfExists('news');
    }
};
