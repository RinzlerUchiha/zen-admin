<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| HireFlow Phase 2 — posting channels.
|
| Where each job posting was advertised, and what came back from each place.
| One row per posting per channel. HireFlow does not publish to LinkedIn,
| JobStreet or PhilJobNet — HR posts by hand and ticks it here — so this
| records HR's own account of where it went, plus the traffic that returned
| through the tagged Careers link for that place.
|
| A row, not a JSON column on tbl_job_posting. Two reasons:
|
|   - visits and applications are counted with an atomic increment. A JSON
|     blob is read-modify-write, so two visits landing together lose one.
|   - "applications from LinkedIn across every posting" stays a GROUP BY
|     instead of scanning and decoding JSON on every row.
|
| Lives on the DEFAULT connection beside tbl_job_posting. The Careers portal
| (zen-applicants) reaches it through its own `zen` connection, which is the
| same database. Consistent with the rest of HireFlow, the link to
| tbl_job_posting is a plain integer with no FK constraint.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_job_posting_channel', function (Blueprint $table) {
            $table->id();

            // References tbl_job_posting.id (plain int, no FK)
            $table->unsignedBigInteger('job_posting_id');

            // A key from config/posting_channels.php — or one that is not,
            // when HR records somewhere the catalogue does not list yet.
            // Stored as text so a place to advertise never waits on a deploy.
            $table->string('channel_key', 40);

            // HR's tick: have they actually posted it there? Only meaningful
            // for the places HR posts by hand; `direct` and `walk_in` are not
            // posted to and stay Not Posted.
            $table->enum('state', ['Not Posted', 'Posted'])->default('Not Posted');

            // When HR said they posted it, and the URL of the post itself, so
            // they can find it again to take it down. Ours to store, not ours
            // to read — nothing fetches this.
            $table->dateTime('posted_at')->nullable();
            $table->string('external_url', 500)->nullable();

            /*
            | HireFlow's side of the trip, and only that.
            |
            |   visits        openings of OUR Careers page that arrived with
            |                 this channel's tag
            |   applications  of those, the ones that went on to apply
            |
            | Never the platform's own impressions or views: we have no API,
            | and presenting these as though we did would be a lie in a
            | column heading. Untagged arrivals are counted under `direct`,
            | so these are a floor, not a census.
            */
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('applications')->default(0);

            $table->timestamps();

            // One row per channel per posting — this is what makes an
            // increment safe to aim at a single row.
            $table->unique(['job_posting_id', 'channel_key'], 'tbl_job_posting_channel_unique');

            // "everything that came through LinkedIn" across all postings.
            $table->index('channel_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_job_posting_channel');
    }
};
