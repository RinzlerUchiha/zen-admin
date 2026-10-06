{{--
  | The dialogs the Interview Details tab opens.
  |
  | Kept out of the page so the page itself reads as the workflow rather than
  | as a stack of forms. Each one asks for exactly what its action needs and
  | says what the action will do — particularly the two that cannot be undone.
--}}

{{-- Arranging or moving an interview. --}}
<div class="modal fade" id="ivScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivScheduleForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="ivScheduleTitle">Arrange interview</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="text-muted mb-2" id="ivScheduleWho"></p>

                <label class="form-label" for="ivScheduleAt">Date and time</label>
                <input type="datetime-local" class="form-control form-control-sm" id="ivScheduleAt" required>

                <div class="row g-2 mt-1">
                    <div class="col-sm-5">
                        <label class="form-label" for="ivDuration">Minutes</label>
                        <input type="number" class="form-control form-control-sm" id="ivDuration" min="1" placeholder="45">
                    </div>
                    <div class="col-sm-7">
                        <label class="form-label" for="ivVenue">Venue</label>
                        <input type="text" class="form-control form-control-sm" id="ivVenue" maxlength="150" placeholder="HR Office">
                    </div>
                </div>

                <label class="form-label mt-2" for="ivLink">Meeting link <span class="text-muted">(optional)</span></label>
                <input type="url" class="form-control form-control-sm" id="ivLink" maxlength="500" placeholder="https://">

                <div class="mt-3" id="ivInterviewerWrap">
                    <label class="form-label" for="ivInterviewer">Interviewer <span class="text-muted">(optional, more can be added later)</span></label>
                    <select class="form-select form-select-sm" id="ivInterviewer">
                        <option value="">Choose an interviewer…</option>
                        @foreach ($employees ?? [] as $emp)
                            <option value="{{ $emp['empno'] }}">{{ $emp['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-check mt-3" id="ivCopyWrap" hidden>
                    <input class="form-check-input" type="checkbox" id="ivCopyInterviewers" checked>
                    <label class="form-check-label" for="ivCopyInterviewers">Keep the same interviewers</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Adding an interviewer to an existing interview. --}}
<div class="modal fade" id="ivAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivAssignForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6">Add interviewer</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <label class="form-label" for="ivAssignWho">Interviewer</label>
                <select class="form-select form-select-sm" id="ivAssignWho" required>
                    <option value="">Choose an interviewer…</option>
                    @foreach ($employees ?? [] as $emp)
                        <option value="{{ $emp['empno'] }}">{{ $emp['name'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">They will be able to record their own evaluation and notes for this interview.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Cancelling, which always needs a reason. --}}
<div class="modal fade" id="ivCancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivCancelForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6">Cancel interview</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <label class="form-label" for="ivCancelReason">Why is it being cancelled? <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="ivCancelReason" maxlength="300" required>
                <div class="form-text">The interview is kept on the record, marked cancelled.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Back</button>
                <button type="submit" class="btn btn-sm btn-danger">Cancel interview</button>
            </div>
        </form>
    </div>
</div>

{{-- An observation. Deliberately lighter than an evaluation. --}}
<div class="modal fade" id="ivNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivNoteForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="ivNoteTitle">Add a note</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="text-muted mb-2">
                    What happened or was observed — a schedule change, something the candidate
                    mentioned, how the interview was conducted. This is not the formal evaluation.
                </p>
                <label class="form-label" for="ivNoteBody">Note</label>
                <textarea class="form-control form-control-sm" id="ivNoteBody" rows="4" maxlength="5000" required></textarea>
                <div class="form-text">Saved under your name. Only you can change your own notes.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Save note</button>
            </div>
        </form>
    </div>
</div>

{{-- The formal evaluation, and its correction. --}}
<div class="modal fade" id="ivEvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivEvalForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="ivEvalTitle">Record evaluation</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="mb-2" id="ivEvalWho"></p>

                <div class="alert alert-warning py-2" id="ivEvalCorrectionNotice" hidden>
                    This replaces your submitted evaluation. The original is kept and stays readable.
                    <b>A correction is submitted immediately and cannot be edited afterwards.</b>
                </div>

                <label class="form-label">Outcome for this round</label>
                <div class="btn-group d-flex mb-2" role="group">
                    <input type="radio" class="btn-check" name="ivOutcome" id="ivOutProceed" value="proceed">
                    <label class="btn btn-sm btn-outline-success" for="ivOutProceed">Proceed</label>

                    <input type="radio" class="btn-check" name="ivOutcome" id="ivOutHold" value="hold">
                    <label class="btn btn-sm btn-outline-warning" for="ivOutHold">Hold</label>

                    <input type="radio" class="btn-check" name="ivOutcome" id="ivOutNo" value="do_not_proceed">
                    <label class="btn btn-sm btn-outline-danger" for="ivOutNo">Do Not Proceed</label>
                </div>
                <div class="form-text mb-3">
                    About this round only — whether they go on to the next step. Not a hiring decision.
                </div>

                <label class="form-label" for="ivRecommendation">Recommendation</label>
                <textarea class="form-control form-control-sm" id="ivRecommendation" rows="2" maxlength="2000"></textarea>

                <label class="form-label mt-2" for="ivRemarks">Remarks</label>
                <textarea class="form-control form-control-sm" id="ivRemarks" rows="3" maxlength="2000"></textarea>

                <div id="ivReasonWrap" hidden>
                    <label class="form-label mt-2" for="ivCorrectionReason">Why are you correcting this? <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="ivCorrectionReason" maxlength="300">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="ivEvalDraft">Save draft</button>
                <button type="submit" class="btn btn-sm btn-primary" id="ivEvalSubmit">Submit</button>
            </div>
        </form>
    </div>
</div>

{{-- HR's decision for the round. --}}
<div class="modal fade" id="ivCompleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="ivCompleteForm">
            <div class="modal-header">
                <h1 class="modal-title fs-6" id="ivCompleteTitle">Record outcome</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)">
                <p class="text-muted mb-3">
                    Your decision for this round, informed by the interviewers but not decided by them.
                    <b>This does not move the application</b> — that is a separate step.
                </p>
                <label class="form-label" for="ivCompleteResult">What did this round conclude?</label>
                <input type="text" class="form-control form-control-sm" id="ivCompleteResult" maxlength="100"
                    placeholder="e.g. Proceed to exams">
                <label class="form-label mt-2" for="ivCompleteNotes">Notes <span class="text-muted">(optional)</span></label>
                <textarea class="form-control form-control-sm" id="ivCompleteNotes" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Record outcome</button>
            </div>
        </form>
    </div>
</div>

{{-- Earlier revisions of one interviewer's evaluation, read-only. --}}
<div class="modal fade" id="ivHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-6">Evaluation history</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="font-size:var(--zn-fs)" id="ivHistoryBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
