

<style>
    #manpower-list {
        font-size: 13px;
    }
    .mpr-tab-badge {
        font-size: 11px;
        margin-left: 4px;
    }
</style>

<script type="text/javascript">
    let currentStat = 'draft';

    $(function () {
        load_counts();
        load_manpower_list(currentStat);

        $('#modal-mpr-form').on('shown.bs.modal', async function (e) {
            let btn = $(e.relatedTarget);
            let id = btn.data('id');

            resetMprForm();

            if (id) {
                $('#mpr-id').val(id);
                try {
                    const response = await fetch('/recruitment/manpower/' + id);
                    const data = await response.json();
                    populateMprForm(data);
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to load request.');
                }
            }
        });

        $('#form-mpr').submit(async function (e) {
            e.preventDefault();
            $('#mpr-err').html('');

            let submitMode = $(document.activeElement).data('submit-mode') || 'draft';

            let formData = new FormData();
            formData.append('id', $('#mpr-id').val());
            formData.append('replacement', JSON.stringify(collectSlots('replacement')));
            formData.append('additional', JSON.stringify(collectSlots('additional')));
            formData.append('nonnegotiable', $('#mpr-nonnegotiable').val());
            formData.append('submit_mode', submitMode);

            let response = await fetch('/recruitment/manpower/save', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let result = await response.json();

            if (response.ok && result.success) {
                $('#modal-mpr-form').modal('hide');
                alert('Saved');
                load_counts();
                load_manpower_list(currentStat);
            } else {
                $('#mpr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        $('#modal-mpr-view').on('shown.bs.modal', async function (e) {
            let btn = $(e.relatedTarget);
            let id = btn.data('id');
            $('#mpr-view-id').val(id);

            try {
                const response = await fetch('/recruitment/manpower/' + id);
                const data = await response.json();
                populateMprView(data);
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load request.');
            }
        });

        $('#btn-mpr-request-update, #btn-mpr-request-cancel').click(function () {
            $('#mpr-view-action').val($(this).attr('id') === 'btn-mpr-request-cancel' ? 'cancel' : 'edit');
            $('#mpr-view-update-panel').removeClass('d-none');
        });

        $('#form-mpr-update').submit(async function (e) {
            e.preventDefault();

            let id = $('#mpr-view-id').val();
            let action = $('#mpr-view-action').val();
            let reason = $('#mpr-view-update-reason').val();

            let response = await fetch('/recruitment/manpower/' + id + '/request-update', {
                method: 'POST',
                body: new URLSearchParams({ action: action, reason: reason }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });

            let result = await response.json();

            if (response.ok && result.success) {
                $('#modal-mpr-view').modal('hide');
                alert('Request submitted');
                load_counts();
                load_manpower_list(currentStat);
            } else {
                alert('Error: ' + result.error);
            }
        });

        $('#mpr-add-replacement').click(() => addSlotRow('replacement'));
        $('#mpr-add-additional').click(() => addSlotRow('additional'));
    });

    function resetMprForm() {
        $('#mpr-id').val('');
        $('#mpr-nonnegotiable').val('');
        $('#mpr-replacement-rows').html('');
        $('#mpr-additional-rows').html('');
        $('#mpr-err').html('');
    }

    function populateMprForm(data) {
        $('#mpr-nonnegotiable').val(data.mp_nonnegotiable);

        (data.replacement_slots || []).forEach(row => addSlotRow('replacement', row));
        (data.additional_slots || []).forEach(row => addSlotRow('additional', row));
    }

    function addSlotRow(type, values = []) {
        let idx = $(`#mpr-${type}-rows tr`).length;
        let row = `
            <tr>
                <td><input type="text" class="form-control form-control-sm slot-position" value="${values[0] || ''}" placeholder="Position"></td>
                <td><input type="number" class="form-control form-control-sm slot-count" value="${values[1] || 1}" min="1"></td>
                <td><input type="text" class="form-control form-control-sm slot-reason" value="${values[2] || ''}" placeholder="Reason"></td>
                <td><input type="date" class="form-control form-control-sm slot-date" value="${values[3] || ''}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()"><i class="fa fa-times"></i></button></td>
            </tr>`;
        $(`#mpr-${type}-rows`).append(row);
    }

    function collectSlots(type) {
        let rows = [];
        $(`#mpr-${type}-rows tr`).each(function () {
            rows.push({
                position: $(this).find('.slot-position').val(),
                count: $(this).find('.slot-count').val(),
                reason: $(this).find('.slot-reason').val(),
                date: $(this).find('.slot-date').val()
            });
        });
        return rows;
    }

    function populateMprView(data) {
        $('#mpr-view-status').text(data.mp_status);
        $('#mpr-view-dtprepared').text(data.mp_dtprepared);
        $('#mpr-view-nonnegotiable').text(data.mp_nonnegotiable || '-');

        let slotHtml = (label, slots) => {
            if (!slots || slots.length === 0) return '';
            let html = `<h6 class="mt-2">${label}</h6><ul class="mb-0">`;
            slots.forEach(s => {
                html += `<li>${s[0]} — qty ${s[1]} (${s[2] || 'no reason given'})</li>`;
            });
            return html + '</ul>';
        };

        $('#mpr-view-slots').html(
            slotHtml('Replacement', data.replacement_slots) +
            slotHtml('Additional', data.additional_slots)
        );

        $('#mpr-view-update-panel').addClass('d-none');
        $('#mpr-view-update-reason').val('');

        if (data.mp_status === 'approved') {
            $('#mpr-view-actions').removeClass('d-none');
        } else {
            $('#mpr-view-actions').addClass('d-none');
        }
    }

    function load_counts() {
        fetch('/recruitment/manpower/counts')
            .then(res => res.json())
            .then(data => {
                ['draft', 'pending', 'approved', 'declined', 'cancelled'].forEach(stat => {
                    $(`#mpr-badge-${stat}`).text(data[stat] ?? 0);
                });
            });
    }

    function load_manpower_list(stat) {
        currentStat = stat;
        $('#manpower-list').html('Loading...');

        fetch('/recruitment/manpower/list/' + stat)
            .then(res => res.text())
            .then(html => {
                $('#manpower-list').html(html);
                $('#manpower-list > table').DataTable({
                    scrollY: '55vh',
                    scrollCollapse: true,
                    lengthMenu: [50, 100, { label: 'All', value: -1 }],
                    ordering: false
                });
            })
            .catch(err => console.error('Error fetching the list:', err));
    }
</script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Manpower Requests</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-mpr-form">
            New Request
        </button>
    </div>

    <ul class="nav nav-tabs mb-2" id="mpr-tabs">
        @foreach (['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'declined' => 'Declined', 'cancelled' => 'Cancelled'] as $stat => $label)
            <li class="nav-item">
                <a class="nav-link {{ $stat == 'draft' ? 'active' : '' }}" href="#" onclick="load_manpower_list('{{ $stat }}'); $('#mpr-tabs .nav-link').removeClass('active'); $(this).addClass('active'); return false;">
                    {{ $label }} <span class="badge bg-secondary mpr-tab-badge" id="mpr-badge-{{ $stat }}">0</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div id="manpower-list"></div>
</div>

<!-- Form Modal: create + revise -->
<div class="modal fade" id="modal-mpr-form" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Manpower Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-mpr">
                <div class="modal-body">
                    <div id="mpr-err"></div>
                    <input type="hidden" id="mpr-id" value="">

                    <h6>Replacement Slots</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Position</th><th>Count</th><th>Reason</th><th>Date Needed</th><th></th></tr>
                        </thead>
                        <tbody id="mpr-replacement-rows"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="mpr-add-replacement">+ Add Replacement</button>

                    <h6>Additional Slots</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Position</th><th>Count</th><th>Reason</th><th>Date Needed</th><th></th></tr>
                        </thead>
                        <tbody id="mpr-additional-rows"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="mpr-add-additional">+ Add Additional</button>

                    <div class="mb-3">
                        <label class="form-label">Non-negotiables</label>
                        <textarea id="mpr-nonnegotiable" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline-primary" data-submit-mode="draft">Save as Draft</button>
                    <button type="submit" class="btn btn-primary" data-submit-mode="pending">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal: read-only + update/cancel toggle -->
<div class="modal fade" id="modal-mpr-view" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Request Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mpr-view-id" value="">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Status:</strong> <span id="mpr-view-status"></span></div>
                    <div class="col-md-4"><strong>Date Prepared:</strong> <span id="mpr-view-dtprepared"></span></div>
                </div>
                <div id="mpr-view-slots"></div>
                <div class="mb-2">
                    <strong>Non-negotiables:</strong>
                    <p id="mpr-view-nonnegotiable"></p>
                </div>

                <div id="mpr-view-actions" class="d-none border-top pt-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-mpr-request-update">Request Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-mpr-request-cancel">Request Cancel</button>

                    <div id="mpr-view-update-panel" class="d-none mt-2">
                        <form id="form-mpr-update">
                            <input type="hidden" id="mpr-view-action" value="">
                            <div class="mb-2">
                                <label class="form-label">Reason</label>
                                <textarea id="mpr-view-update-reason" class="form-control form-control-sm" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

