<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * tbl_job_posting lives on the DEFAULT (zen-admin) connection, not hrd2.
     * It references HireFlow's tbl_manpower_request_position and
     * tbl_manpower_jobspec rows by plain integer ID only — no cross-schema
     * FK constraints, consistent with HireFlow's own architecture.
     */
    public function up(): void
    {
        Schema::create('tbl_job_posting', function (Blueprint $table) {
            $table->id();

            // References hrd2.tbl_manpower_request_position.id (plain int, no FK)
            $table->unsignedBigInteger('request_position_id');

            // References hrd2.tbl_manpower_jobspec.jspec_id (plain int, no FK)
            $table->unsignedBigInteger('jobspec_id');

            // Optional posting-specific copy that can differ from the
            // internal jobspec text (e.g. externally-facing title/description)
            $table->string('posting_title')->nullable();
            $table->text('posting_description')->nullable();

            // Draft: created but not yet public
            // Published: live, accepting applications
            // Closed: no longer accepting applications
            $table->enum('status', ['Draft', 'Published', 'Closed'])->default('Draft');

            $table->timestamp('posted_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Who created/posted it (HR employee number, matches HireFlow's
            // convention of storing emp_no as a plain string, not a User FK)
            $table->string('created_by', 20)->nullable();

            $table->timestamps();

            $table->index('request_position_id');
            $table->index('jobspec_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_job_posting');
    }
};