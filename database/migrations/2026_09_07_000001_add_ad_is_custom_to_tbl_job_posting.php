<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ad_is_custom = 0  → public_description is machine-composed from the
     *                     jobspec and may be refreshed automatically.
     * ad_is_custom = 1  → a person edited the wording; never overwrite it.
     *
     * The flag is derived on save by comparing the submitted ad against a
     * freshly composed one, so it stays correct without anyone maintaining it.
     */
    public function up(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->boolean('ad_is_custom')->default(false)->after('public_description');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->dropColumn('ad_is_custom');
        });
    }
};
