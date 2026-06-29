<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('hrd2')->statement("
            ALTER TABLE tbl_manpower
            MODIFY mp_reviewedby VARCHAR(20) NULL DEFAULT NULL,
            MODIFY mp_approvedby VARCHAR(20) NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        // Not reversing to NOT NULL — existing rows may already contain NULLs
        // after this migration runs, which would break a straight rollback.
    }
};