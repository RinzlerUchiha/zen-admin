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
        DB::statement('ALTER TABLE tbl_jobspec
            MODIFY jspec_education TEXT,
            MODIFY jspec_computerskill TEXT,
            MODIFY jspec_tapt TEXT,
            MODIFY jspec_enneagram TEXT,
            MODIFY jspec_learnstyle TEXT,
            MODIFY jspec_career TEXT,
            MODIFY jspec_motivation TEXT,
            MODIFY jspec_personality TEXT,
            MODIFY jspec_ravenl TEXT,
            MODIFY jspec_ravena TEXT,
            MODIFY jspec_ravenh TEXT,
            MODIFY jspec_workexp TEXT
        ');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tbl_jobspec
            MODIFY jspec_education VARCHAR(255),
            MODIFY jspec_computerskill VARCHAR(255),
            MODIFY jspec_tapt VARCHAR(255),
            MODIFY jspec_enneagram VARCHAR(255),
            MODIFY jspec_learnstyle VARCHAR(255),
            MODIFY jspec_career VARCHAR(255),
            MODIFY jspec_motivation VARCHAR(255),
            MODIFY jspec_personality VARCHAR(255),
            MODIFY jspec_ravenl VARCHAR(255),
            MODIFY jspec_ravena VARCHAR(255),
            MODIFY jspec_ravenh VARCHAR(255),
            MODIFY jspec_workexp VARCHAR(255)
        ");
    }
};
