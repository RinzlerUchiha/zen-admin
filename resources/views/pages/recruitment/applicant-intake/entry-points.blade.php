{{--
| Controlled entry points: the office kiosk and job-fair events.
|
| HR activates one, bookmarks the link or prints the QR, and applications made
| through it are recorded with the right source without anyone being asked.
|
| This panel only ever affects where an application is recorded as coming FROM.
| It has no bearing on stage, interviews, exams, documents or hiring.
--}}
<style>
    .ep-wrap { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }

    .ep-card {
        border: 1px solid var(--zn-line);
        border-radius: var(--zn-radius-lg);
        background: var(--zn-surface);
        padding: 14px 16px;
        min-width: 0;
    }

    .ep-head {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: space-between;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }

    .ep-title { font-weight: 700; color: var(--zn-ink); }
    .ep-sub { font-size: var(--zn-fs-sm); color: var(--zn-ink-3); }

    .ep-state {
        font-size: var(--zn-fs-xs);
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .ep-state.is-live { background: var(--hf-done-soft); color: var(--hf-done); }
    .ep-state.is-off { background: var(--zn-surface-2); color: var(--zn-ink-3); }

    /* QR beside the link on a wide card, stacked when there is no room. */
    .ep-body { display: flex; gap: 14px; align-items: flex-start; flex-wrap: wrap; margin-top: 10px; }

    .ep-qr {
        border: 1px solid var(--zn-line-2);
        border-radius: var(--zn-radius);
        background: #fff;
        padding: 6px;
        line-height: 0;
        flex: 0 0 auto;
    }

    .ep-qr canvas { display: block; width: 132px; height: 132px; }

    .ep-link-col { flex: 1 1 180px; min-width: 0; }

    /* The URL must wrap, not widen the card. Without this the token turns into
       one long unbreakable word and pushes the page sideways on a phone. */
    .ep-url {
        font-family: var(--zn-font-mono, monospace);
        font-size: var(--zn-fs-xs);
        background: var(--zn-surface-2);
        border: 1px solid var(--zn-line-2);
        border-radius: var(--zn-radius);
        padding: 6px 8px;
        overflow-wrap: anywhere;
        word-break: break-all;
        max-width: 100%;
    }

    .ep-count { font-weight: 700; color: var(--zn-ink); }

    .ep-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }

    .ep-past { display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }

    .ep-row {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .ep-row.is-past { border-top: 1px solid var(--zn-line-2); padding-top: 6px; }

    .ep-empty { color: var(--zn-ink-3); font-size: var(--zn-fs-sm); padding: 6px 0; }

    @media (max-width: 575.98px) {
        .ep-qr canvas { width: 108px; height: 108px; }
    }
</style>

<div id="ep-alert"></div>

<div class="ep-wrap">
    {{-- The office kiosk: one standing entry, activated once and left on. --}}
    <section class="ep-card" id="ep-walkin">
        <div class="ep-head">
            <span class="ep-title">Walk-in Mode</span>
            <span class="ep-state is-off" id="ep-walkin-state">Inactive</span>
        </div>
        <div class="ep-sub">
            The office PC. Anyone who applies after opening this link is recorded as a
            <strong>Walk-in</strong> — they still register and apply themselves, as normal.
        </div>
        <div id="ep-walkin-body"></div>
    </section>

    {{-- Job fairs: several may run at once, and past ones keep their numbers. --}}
    <section class="ep-card" id="ep-fairs">
        <div class="ep-head">
            <span class="ep-title">Job Fair Mode</span>
            @can('applicant-applications.decide')
                <button type="button" class="btn btn-sm btn-primary" id="ep-new-fair"
                    data-bs-toggle="modal" data-bs-target="#epFairModal">
                    <i class="bi bi-plus-lg"></i> New event
                </button>
            @endcan
        </div>
        <div class="ep-sub">
            One entry per event, so you can tell which fair produced which applications.
        </div>
        <div id="ep-fairs-body"></div>
    </section>
</div>

@can('applicant-applications.decide')
    <div class="modal fade" id="epFairModal" tabindex="-1" aria-labelledby="epFairTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="epFairTitle">New job fair event</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="epFairForm">
                    <div class="modal-body">
                        <div id="epFairError" class="alert alert-danger py-2 small d-none"></div>

                        <label class="form-label small" for="ep-fair-label">Event name <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" id="ep-fair-label" name="label"
                            maxlength="150" placeholder="CDO Job Fair" required>
                        <div class="form-text">Used to tell this fair&rsquo;s applications from another&rsquo;s.</div>

                        <label class="form-label small mt-2" for="ep-fair-date">Event date</label>
                        <input type="date" class="form-control form-control-sm" id="ep-fair-date" name="event_date">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="epFairSubmit">Create &amp; activate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

{{-- Vendored, not from a CDN: the entry URL should not be handed to
     third-party code in an HR user's browser. The library computes modules and
     touches no DOM; qr-render.js does the drawing. --}}
<script src="{{ asset('vendor/qrcode/qrcode-generator.js') }}"></script>
<script src="{{ asset('vendor/qrcode/qr-render.js') }}"></script>
<script>
    $(function () {
        const EP_URLS = {
            index: @json(route('recruitment.applicant-intake.entry-points.index')),
            walkIn: @json(route('recruitment.applicant-intake.entry-points.walk-in')),
            jobFair: @json(route('recruitment.applicant-intake.entry-points.job-fair')),
            base: @json(url('/recruitment/applicant-intake/entry-points')),
        };

        const EP_CAN_EDIT = @json(Gate::allows('applicant-applications.decide'));

        /** Escapes for markup built as a string here. */
        function epEsc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function epAlert(type, message) {
            $('#ep-alert').html(
                '<div class="alert alert-' + type + ' alert-dismissible fade show py-2">' + epEsc(message) +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
            );
        }

        function epDate(value) {
            if (!value) { return ''; }
            const d = new Date(String(value).replace(' ', 'T'));
            return isNaN(d) ? value : d.toLocaleDateString(undefined,
                { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function epPlural(count, word) {
            return count + ' ' + word + (count === 1 ? '' : 's');
        }

        /* A live entry: QR, link, who opened it, and what it has produced. */
        function epLiveBody(entry) {
            if (!entry.url) {
                return '<div class="ep-empty">CAREERS_URL is not set, so no entry link can be built. '
                     + 'Set it in the environment file and reload.</div>';
            }

            return '' +
            '<div class="ep-body">' +
              '<div class="ep-qr"><canvas data-qr-url="' + epEsc(entry.url) + '"></canvas></div>' +
              '<div class="ep-link-col">' +
                '<div class="ep-url">' + epEsc(entry.url) + '</div>' +
                '<div class="ep-sub mt-1">' +
                  '<span class="ep-count">' + entry.applications + '</span> ' +
                  (entry.applications === 1 ? 'application' : 'applications') + ' recorded' +
                '</div>' +
                '<div class="ep-sub">Opened ' + epEsc(epDate(entry.activated_at)) +
                  (entry.activated_by ? ' by ' + epEsc(entry.activated_by) : '') + '</div>' +
              '</div>' +
            '</div>' +
            (EP_CAN_EDIT ? '<div class="ep-actions">' +
              '<button type="button" class="btn btn-sm btn-outline-secondary ep-copy" data-url="' + epEsc(entry.url) + '">Copy link</button>' +
              '<button type="button" class="btn btn-sm btn-outline-danger ep-deactivate" data-id="' + entry.id + '">Deactivate</button>' +
            '</div>' : '');
        }

        function epRenderWalkIn(entry) {
            const $state = $('#ep-walkin-state');

            if (!entry) {
                $state.removeClass('is-live').addClass('is-off').text('Inactive');
                $('#ep-walkin-body').html(
                    '<div class="ep-actions">' +
                    (EP_CAN_EDIT
                        ? '<button type="button" class="btn btn-sm btn-primary" id="ep-activate-walkin">Activate Walk-in Mode</button>'
                        : '<span class="ep-empty">Not active.</span>') +
                    '</div>'
                );
                return;
            }

            $state.removeClass('is-off').addClass('is-live').text('Active');
            $('#ep-walkin-body').html(epLiveBody(entry));
        }

        function epRenderFairs(active, past) {
            let html = '';

            if (!active.length) {
                html += '<div class="ep-empty">No event is running. Create one when you go to a fair.</div>';
            }

            active.forEach(function (entry) {
                html += '<div class="ep-row" style="margin-top:10px;">' +
                          '<div><span class="ep-title">' + epEsc(entry.name) + '</span>' +
                          (entry.event_date ? ' <span class="ep-sub">&middot; ' + epEsc(epDate(entry.event_date)) + '</span>' : '') +
                          '</div>' +
                          '<span class="ep-state is-live">Active</span>' +
                        '</div>' + epLiveBody(entry);
            });

            if (past.length) {
                html += '<div class="ep-sub mt-3" style="font-weight:600;">Past events</div><div class="ep-past">';

                past.forEach(function (entry) {
                    html += '<div class="ep-row is-past">' +
                              '<div>' +
                                '<div>' + epEsc(entry.name) +
                                  (entry.event_date ? ' <span class="ep-sub">&middot; ' + epEsc(epDate(entry.event_date)) + '</span>' : '') +
                                '</div>' +
                                '<div class="ep-sub">Ended ' + epEsc(epDate(entry.deactivated_at)) + ' &middot; ' +
                                  '<span class="ep-count">' + entry.applications + '</span> ' +
                                  (entry.applications === 1 ? 'application' : 'applications') + '</div>' +
                              '</div>' +
                              (EP_CAN_EDIT
                                ? '<button type="button" class="btn btn-sm btn-outline-secondary ep-reactivate" data-id="' + entry.id + '">Reopen</button>'
                                : '') +
                            '</div>';
                });

                html += '</div>';
            }

            $('#ep-fairs-body').html(html);
        }

        /* QR codes are drawn once the markup is in the DOM. */
        function epDrawQrs() {
            document.querySelectorAll('#ep-walkin canvas[data-qr-url], #ep-fairs canvas[data-qr-url]')
                .forEach(function (canvas) {
                    const url = canvas.getAttribute('data-qr-url');

                    if (!window.hfQr || !window.hfQr.draw(canvas, url, 264)) {
                        // The link sits right beside it, so a missing QR is
                        // inconvenient rather than blocking.
                        const note = document.createElement('div');
                        note.className = 'ep-empty';
                        note.textContent = 'QR unavailable — use the link.';
                        canvas.replaceWith(note);
                    }
                });
        }

        function epLoad() {
            $.getJSON(EP_URLS.index)
                .done(function (json) {
                    epRenderWalkIn(json.walk_in);
                    epRenderFairs(json.active_fairs || [], json.past_fairs || []);
                    epDrawQrs();
                })
                .fail(function () {
                    $('#ep-walkin-body, #ep-fairs-body').html(
                        '<div class="ep-empty">Could not load the entry points. Please refresh.</div>');
                });
        }

        async function epPost(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(body || {})
            });

            const data = await res.json().catch(function () { return {}; });

            if (!res.ok) {
                const messages = data.errors ? Object.values(data.errors).flat()
                                             : [data.message || 'That did not work.'];
                throw new Error(messages.join(' '));
            }

            return data;
        }

        $('#ep-walkin').on('click', '#ep-activate-walkin', async function () {
            const $btn = $(this).prop('disabled', true).text('Activating…');

            try {
                const data = await epPost(EP_URLS.walkIn);
                epAlert('success', data.message);
                epLoad();
            } catch (err) {
                epAlert('danger', err.message);
                $btn.prop('disabled', false).text('Activate Walk-in Mode');
            }
        });

        $('#ep-walkin, #ep-fairs').on('click', '.ep-deactivate', async function () {
            if (!confirm('Close this entry point? Its link and QR stop working immediately. '
                       + 'Applications already recorded against it are kept.')) { return; }

            const id = $(this).data('id');
            $(this).prop('disabled', true).text('Closing…');

            try {
                const data = await epPost(EP_URLS.base + '/' + id + '/deactivate');
                epAlert('success', data.message);
            } catch (err) {
                epAlert('danger', err.message);
            }

            epLoad();
        });

        $('#ep-fairs').on('click', '.ep-reactivate', async function () {
            const id = $(this).data('id');
            $(this).prop('disabled', true).text('Reopening…');

            try {
                const data = await epPost(EP_URLS.base + '/' + id + '/reactivate');
                epAlert('success', data.message);
            } catch (err) {
                epAlert('danger', err.message);
            }

            epLoad();
        });

        $('#ep-walkin, #ep-fairs').on('click', '.ep-copy', function () {
            const url = $(this).data('url');
            const $btn = $(this);

            if (!navigator.clipboard) {
                epAlert('warning', 'Select the link and copy it by hand — this browser will not do it for us.');
                return;
            }

            navigator.clipboard.writeText(url)
                .then(function () {
                    $btn.text('Copied');
                    setTimeout(function () { $btn.text('Copy link'); }, 1500);
                })
                .catch(function () {
                    epAlert('warning', 'Could not copy. Select the link and copy it by hand.');
                });
        });

        $('#epFairForm').on('submit', async function (e) {
            e.preventDefault();

            const $btn = $('#epFairSubmit').prop('disabled', true).text('Creating…');
            $('#epFairError').addClass('d-none').text('');

            const payload = {};
            $(this).serializeArray().forEach(function (f) {
                if (f.value) { payload[f.name] = f.value; }
            });

            try {
                const data = await epPost(EP_URLS.jobFair, payload);
                bootstrap.Modal.getInstance(document.getElementById('epFairModal')).hide();
                this.reset();
                epAlert('success', data.message);
                epLoad();
            } catch (err) {
                $('#epFairError').text(err.message).removeClass('d-none');
            } finally {
                $btn.prop('disabled', false).text('Create & activate');
            }
        });

        epLoad();
    });
</script>
