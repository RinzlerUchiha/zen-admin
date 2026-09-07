<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * posting_description stays as the auto-drafted internal jobspec text
     * (HR reference only). public_description holds the hand-written ad
     * that the careers page renders. Nullable + additive: existing rows
     * fall back to the old behaviour until an ad is written.
     */
    public function up(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->text('public_description')->nullable()->after('posting_description');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->dropColumn('public_description');
        });
    }
};
