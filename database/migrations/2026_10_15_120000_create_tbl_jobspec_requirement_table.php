<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Which of a job specification's requirements are mandatory.
|
| WHY THIS IS NOT ALREADY ANSWERED
|
| The existing form asks for "EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED" in a
| single checkbox list, and stores the ticked values joined by "%#". Required
| and preferred are therefore conflated in the data, and no amount of reading
| it can separate them -- the distinction was never captured.
|
| WHY A SEPARATE TABLE
|
| tbl_manpower_jobspec is written by TWO legacy forms
| (zen/manpower/public/jobspec_save.php and zen/compliance/pages/jobspec.php).
| Adding a parallel flag column per criterion would mean changing both, in two
| applications, to keep a column they have no concept of. A separate table is
| additive: neither writer knows or cares that this exists, and both keep
| working untouched.
|
| ABSENCE MEANS PREFERRED
|
| The single most important property here. A criterion with no row is treated
| as PREFERRED, never mandatory. So:
|
|   - matching works from day one with no configuration at all;
|   - no existing job specification is retroactively made stricter;
|   - a gap in the configuration can never cause a rejection.
|
| Nothing disqualifies an applicant by default, which is the whole point.
|
| WHY THE SPECIFICATION AND NOT THE POSTING
|
| A job specification belongs to a position and is reused across every posting
| of it -- tbl_manpower_request_position.jobspec_id is what resolves one.
| Classifying per posting would make HR answer the same questions again for
| every posting of the same role. A nullable posting_id can be added later for
| a per-posting override without restructuring anything here.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_jobspec_requirement', function (Blueprint $table) {
            $table->id();

            /* Not a foreign key: tbl_manpower_jobspec is a legacy table with
               its own lifecycle, and a constraint here would make this
               migration able to fail on data it does not own. */
            $table->unsignedInteger('jspec_id');

            /*
            | Which requirement this is about -- 'education', 'workexp',
            | 'computerskill', 'certification' and so on. A key rather than a
            | column reference, so a criterion that is assembled from more than
            | one jobspec field can still be classified once.
            */
            $table->string('criterion_key', 40);

            $table->enum('importance', ['mandatory', 'preferred'])->default('preferred');

            /* Why HR classified it this way, for whoever reads the shortlist
               six months from now. */
            $table->string('note', 255)->nullable();

            $table->string('created_by', 20)->nullable();
            $table->string('updated_by', 20)->nullable();
            $table->timestamps();

            /* One answer per criterion per specification. */
            $table->unique(['jspec_id', 'criterion_key'], 'tbl_jobspec_requirement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_jobspec_requirement');
    }
};
