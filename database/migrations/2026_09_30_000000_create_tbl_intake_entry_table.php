<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Controlled entry points: the office kiosk and job-fair events.
|
| HR activates one and gets a tokenised URL (and a QR code pointing at it).
| Anyone who applies after arriving through that link has their application
| recorded with the matching source, without being asked to pick one — which
| is the whole point, since applicants do not reliably know or say how they
| found us.
|
| This table is ONLY about where an application came from. It says nothing
| about hiring stage, interviews, exams or outcome.
|
| Lives on the DEFAULT connection beside tbl_job_posting, because HireFlow
| owns it and HR manages it from zen-admin. The Careers portal reads it
| through its own `zen` connection, exactly as it reads tbl_job_posting_channel.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_intake_entry', function (Blueprint $table) {
            $table->id();

            /*
            | Which source this entry point writes: 'walk_in' or 'job_fair'.
            | A key from config/posting_channels.php, not a free label — a
            | string rather than an enum so a new kind is a config change
            | rather than a migration, matching how channels already work.
            */
            $table->string('kind', 40);

            /*
            | What HR calls it. A job fair needs one ("CDO Job Fair, Oct
            | 2026") so past events can be told apart; the standing office
            | kiosk does not, so this may be empty.
            */
            $table->string('label', 150)->nullable();

            /* When the event happened, for job fairs. Not a deadline. */
            $table->date('event_date')->nullable();

            /*
            | The secret in the URL. High-entropy and single-purpose: the
            | worst it can do is mislabel an application's source, so it is
            | stored as-is and looked up directly rather than hashed, which
            | would force a scan of every row on each visit.
            |
            | Unique, and reissued on every activation — so a QR code printed
            | for a previous run of the same event cannot be revived.
            */
            $table->string('token', 64)->unique();

            $table->string('activated_by', 20)->nullable();
            $table->timestamp('activated_at')->nullable();

            /*
            | The on/off switch. NULL means live. Deactivating sets this and
            | the URL stops working on the next request.
            |
            | Rows are NEVER deleted: applications point at them, and an
            | event's history has to survive the event being closed.
            */
            $table->string('deactivated_by', 20)->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();

            /* "Which entries are live?" — the question asked on every visit. */
            $table->index(['kind', 'deactivated_at'], 'tbl_intake_entry_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_intake_entry');
    }
};
