<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();

            $table->unique(['document_category_id', 'locale'], 'doc_cat_trn_cat_locale_uq');
        });

        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('document_tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tag_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();

            $table->unique(['document_tag_id', 'locale'], 'doc_tag_trn_tag_locale_uq');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->constrained();
            $table->string('status', 50);
            $table->string('file_type', 50)->nullable();
            $table->date('document_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('document_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'locale'], 'doc_trn_doc_locale_uq');
            $table->unique(['locale', 'slug'], 'doc_trn_locale_slug_uq');
        });

        Schema::create('document_document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['document_id', 'document_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_document_tag');
        Schema::dropIfExists('document_translations');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_tag_translations');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('document_category_translations');
        Schema::dropIfExists('document_categories');
    }
};
