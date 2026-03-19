<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('procurement_type', 100);
            $table->string('status', 50);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closing_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('procurement_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['procurement_id', 'locale'], 'proc_trn_proc_locale_uq');
            $table->unique(['locale', 'slug'], 'proc_trn_locale_slug_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_translations');
        Schema::dropIfExists('procurements');
    }
};
