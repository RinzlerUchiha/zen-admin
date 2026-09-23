<?php

use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\Ad\JobShortDescription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A short, plain-text job description for publishing a posting anywhere the
     * full public ad does not fit (job boards, social posts, link previews).
     * Nullable and additive.
     *
     * Existing postings get a drafted one, so every posting has a summary from
     * the start. Only empty values are filled; nothing already written is
     * touched, and a posting whose Job Spec cannot be read is simply left empty.
     */
    public function up(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->string('short_description', JobShortDescription::MAX)->nullable()->after('public_description');
        });

        $composer = new JobShortDescription();

        JobPosting::with('hireflowPosition')->whereNull('short_description')->get()
            ->each(function (JobPosting $posting) use ($composer) {
                try {
                    if ($posting->hireflowPosition && $posting->hireflowPosition->jobSpec()) {
                        // Query builder: fills the column without touching updated_at.
                        DB::table('tbl_job_posting')->where('id', $posting->id)
                            ->update(['short_description' => $composer->composeFor($posting->hireflowPosition)]);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tbl_job_posting', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }
};
