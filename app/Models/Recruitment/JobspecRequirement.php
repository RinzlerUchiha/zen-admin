<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * Whether one of a job specification's requirements is mandatory.
 *
 * ABSENCE MEANS PREFERRED
 *
 * The rule the whole design rests on. A criterion with no row here is
 * preferred, never mandatory — so matching works on an unconfigured
 * specification, no existing specification is retroactively made stricter, and
 * a gap in the configuration can never cause a rejection.
 */
class JobspecRequirement extends Model
{
    protected $table = 'tbl_jobspec_requirement';

    protected $fillable = [
        'jspec_id', 'criterion_key', 'importance', 'note', 'created_by', 'updated_by',
    ];

    public const MANDATORY = 'mandatory';
    public const PREFERRED = 'preferred';

    /**
     * How each criterion of one specification is classified.
     *
     * Returns only what HR has actually recorded. Callers treat a missing key
     * as preferred — deliberately, so this never invents an answer and the
     * caller can tell "preferred" from "nobody has said".
     *
     * @return array<string,string> criterion_key => importance
     */
    public static function importanceFor(int $jspecId): array
    {
        return self::where('jspec_id', $jspecId)
            ->pluck('importance', 'criterion_key')
            ->all();
    }

    /** Record how a criterion is classified, replacing any earlier answer. */
    public static function classify(int $jspecId, string $criterionKey, string $importance, ?string $note, ?string $empno): self
    {
        $importance = $importance === self::MANDATORY ? self::MANDATORY : self::PREFERRED;

        $row = self::firstOrNew([
            'jspec_id' => $jspecId,
            'criterion_key' => $criterionKey,
        ]);

        $row->importance = $importance;
        $row->note = $note;
        $row->updated_by = $empno;
        $row->created_by = $row->created_by ?: $empno;
        $row->save();

        return $row;
    }
}
