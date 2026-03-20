<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->json('content_blocks')->nullable()->after('content');
        });

        Schema::table('news_translations', function (Blueprint $table) {
            $table->json('content_blocks')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });

        Schema::table('news_translations', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }
};
