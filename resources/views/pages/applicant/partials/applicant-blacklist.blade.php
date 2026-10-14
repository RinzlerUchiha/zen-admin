{{--
    Whether this applicant is barred from new applications, and the history of
    that decision.

    Sits above everything else on the profile: it changes how the rest of the
    page should be read, so finding it further down -- or in a tab -- would be
    finding it too late.

    Nothing here touches the applicant's existing applications, interviews,
    assessments or documents. Those record things that happened and they stand.
    A blacklist stops the NEXT application being made; concluding work already
    under way is a separate decision HR makes deliberately.
--}}
@php
    $blHistory = collect($blacklistHistory ?? []);
    $blPast = $blHistory->filter(fn ($row) => $row->lifted_at !== null);
@endphp

@if (!empty($blacklist))
    <div class="ap-blacklist" role="status">
        <i class="bi bi-slash-circle-fill ap-blacklist-ico" aria-hidden="true"></i>
        <div class="ap-blacklist-main">
            <p class="ap-blacklist-title">This applicant is blacklisted</p>
            <p class="ap-blacklist-reason">{{ $blacklist->reason }}</p>
            <p class="ap-blacklist-meta">
                By {{ $blacklist->blacklisted_by }}
                on {{ \Illuminate\Support\Carbon::parse($blacklist->blacklisted_at)->format('M j, Y g:i A') }}
            </p>
            <p class="ap-blacklist-note">
                They cannot start a new application, through Careers or at intake.
                Everything already on this profile is unchanged.
            </p>
        </div>
        @can('applicant-applications.decide')
            <button type="button" class="btn btn-sm btn-outline-secondary flex-none"
                    data-bs-toggle="modal" data-bs-target="#apBlacklistLiftModal">
                Lift
            </button>
        @endcan
    </div>
@endif

@if ($blPast->isNotEmpty())
    <details class="ap-bl-history">
        <summary>
            Blacklist history ({{ $blPast->count() }} {{ \Illuminate\Support\Str::plural('earlier decision', $blPast->count()) }})
        </summary>
        <ol>
            @foreach ($blPast as $row)
                <li>
                    Blacklisted by {{ $row->blacklisted_by }}
                    on {{ \Illuminate\Support\Carbon::parse($row->blacklisted_at)->format('M j, Y') }}
                    — “{{ $row->reason }}”.
                    Lifted by {{ $row->lifted_by }}
                    on {{ \Illuminate\Support\Carbon::parse($row->lifted_at)->format('M j, Y') }}
                    — “{{ $row->lift_reason }}”.
                </li>
            @endforeach
        </ol>
    </details>
@endif

@can('applicant-applications.decide')
    @if (empty($blacklist))
        <div class="modal fade" id="apBlacklistModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST"
                      action="{{ route('applicant.blacklist.store', ['id' => $applicant->app_id]) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Blacklist this applicant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" style="font-size: var(--zn-fs-sm)">
                            They will not be able to start a new application, through Careers or at intake.
                            Their existing applications, interviews, assessments and documents are left exactly as they are.
                        </p>
                        <label class="form-label" for="ap-bl-reason">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ap-bl-reason" name="reason" rows="3"
                                  maxlength="2000" required
                                  placeholder="Why this applicant should not be taken on for now."></textarea>
                        <div class="form-text">Kept on the record, with your name and the date.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Blacklist applicant</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="modal fade" id="apBlacklistLiftModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST"
                      action="{{ route('applicant.blacklist.lift', ['id' => $applicant->app_id]) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Lift this blacklist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" style="font-size: var(--zn-fs-sm)">
                            They will be able to apply again. The original decision stays on the record —
                            lifting it is recorded alongside, not in place of it.
                        </p>
                        <label class="form-label" for="ap-bl-lift-reason">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ap-bl-lift-reason" name="reason" rows="3"
                                  maxlength="2000" required
                                  placeholder="Why the blacklist no longer applies."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Lift blacklist</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
