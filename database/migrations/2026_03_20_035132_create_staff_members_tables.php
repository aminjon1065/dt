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
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('staff_members')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('office_location')->nullable();
            $table->boolean('show_email_publicly')->default(false);
            $table->boolean('show_phone_publicly')->default(true);
            $table->string('status', 50)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('staff_member_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->string('position')->nullable();
            $table->longText('bio')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['staff_member_id', 'locale'], 'staff_trn_member_locale_uq');
            $table->unique(['locale', 'slug'], 'staff_trn_locale_slug_uq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_member_translations');
        Schema::dropIfExists('staff_members');
    }
};
