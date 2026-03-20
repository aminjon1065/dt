<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_translations', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('summary');
            $table->json('content_blocks')->nullable()->after('content');
            $table->string('seo_title')->nullable()->after('content_blocks');
            $table->text('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('document_translations', function (Blueprint $table) {
            $table->dropColumn([
                'content',
                'content_blocks',
                'seo_title',
                'seo_description',
            ]);
        });
    }
};
