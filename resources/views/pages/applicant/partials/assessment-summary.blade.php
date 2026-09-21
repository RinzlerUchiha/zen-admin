{{--
    The header of an assessment result tab (HireFlow 2.5 · read-only).

    What HR and approvers need first: which assessment, whether it was taken,
    when, how long it took, and — for the graded tests — the score. When there
    is no result, it says so instead of showing an answer sheet.
--}}
@php
    $s = $assessmentSummary ?? null;
    $score = null;
    if ($s && $s->hasResult && $s->kind === 'aptitude') {
        $correct = match ($s->key) {
            'basic_math' => $basicMathResult ?? null,
            'abstract_reasoning' => $abstractReasoningResult ?? null,
            'maya' => $mayaResult['totalOverallSet'] ?? null,
            default => null,
        };
        if ($correct !== null && $s->items) {
            $score = ['correct' => (int) $correct, 'of' => $s->items, 'pct' => round($correct / $s->items * 100)];
        }
    }
@endphp

@if ($s)
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="flex-grow-1">
                <div class="small text-body-secondary text-uppercase">{{ $s->kind === 'aptitude' ? 'Aptitude test · graded' : 'Questionnaire' }}</div>
                <h5 class="mb-1">{{ $s->label }}
                    <span class="hf-chip {{ $s->badge[1] ?? 'hf-chip-lock' }} align-middle">{{ $s->badge[0] ?? '' }}</span>
                </h5>
                <div class="small text-body-secondary">
                    @if ($s->takenOn)
                        {{ $s->status === 'timed_out' ? 'Ended' : 'Completed' }}
                        {{ $s->takenAtKnown ? $s->takenOn->format('M j, Y g:i A') : $s->takenOn->format('M j, Y') }}
                    @endif
                    @if ($s->attempt)
                        · {{ intdiv($s->attempt->time_used_seconds, 60) }} min {{ $s->attempt->time_used_seconds % 60 }} s of {{ intdiv($s->attempt->duration_seconds, 60) }} min
                    @endif
                    @if ($s->status === 'timed_out' && $s->hasResult)
                        · submitted automatically when time ran out
                    @endif
                </div>
            </div>
            @if ($score)
                <div class="text-end">
                    <div class="fs-3 fw-semibold lh-1">{{ $score['correct'] }}<span class="fs-6 text-body-secondary"> / {{ $score['of'] }}</span></div>
                    <div class="small text-body-secondary">{{ $score['pct'] }}% correct</div>
                </div>
            @endif
        </div>
    </div>

    @unless ($s->hasResult)
        <div class="alert alert-light border d-flex gap-2 align-items-start" role="status">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                @switch($s->status)
                    @case('active') The applicant is taking this assessment now. The result appears here once they submit. @break
                    @case('interrupted') This assessment was interrupted with {{ $s->attempt?->minutes_left }} min left. The applicant needs a new access code to finish it. @break
                    @case('timed_out') Time ran out before it was complete, so there is no result. @break
                    @default The applicant has not taken this assessment yet.
                @endswitch
            </div>
        </div>
    @endunless
@endif
