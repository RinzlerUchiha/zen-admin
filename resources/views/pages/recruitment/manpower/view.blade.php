<div class="modal fade" id="modal-manpower-view" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="manpower-view-body"></div>
            <div class="modal-footer" id="manpower-view-footer"></div>
        </div>
    </div>
</div>

<div class="d-none" id="manpower-view-update-toggle">
    <hr>
    <label class="form-label">Reason</label>
    <textarea id="manpower-update-reason" class="form-control mb-2"></textarea>
    <button type="button" class="btn btn-primary btn-sm" id="btn-manpower-submit-update">Submit</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="$('#manpower-view-update-toggle').addClass('d-none')">Cancel</button>
</div>

<script>
let currentManpowerId = null;

async function viewManpowerRequest(id) {
    currentManpowerId = id;
    const response = await fetch("{{ url('recruitment/manpower') }}/" + id);
    const data = await response.json();

    function renderSlotRows(slots) {
        if (!slots || !slots.length) return '<tr><td colspan="4" class="text-muted text-center">None</td></tr>';
        return slots.map(s => `<tr>
            <td>${s[0] ?? ''}</td>
            <td>${s[1] ?? ''}</td>
            <td>${s[2] ?? ''}</td>
            <td>${s[3] ?? ''}</td>
        </tr>`).join('');
    }

    let html = '<p><strong>Status:</strong> ' + (data.mp_status || '') + '</p>';
    html += '<h6>Replacement</h6>';
    html += '<table class="table table-sm"><thead><tr><th>Position</th><th>Count</th><th>Reason</th><th>Date Needed</th></tr></thead><tbody>';
    html += renderSlotRows(data.replacement_slots);
    html += '</tbody></table>';
    html += '<h6>Additional</h6>';
    html += '<table class="table table-sm"><thead><tr><th>Position</th><th>Count</th><th>Reason</th><th>Date Needed</th></tr></thead><tbody>';
    html += renderSlotRows(data.additional_slots);
    html += '</tbody></table>';
    if (data.mp_nonnegotiable) {
        html += '<p><strong>Non-negotiable:</strong><br>' + data.mp_nonnegotiable.replace(/\n/g, '<br>') + '</p>';
    }
    $('#manpower-view-body').html(html);
    $('#manpower-view-update-toggle').addClass('d-none');

    let footer = '';
    if (data.mp_status === 'pending') {
        footer += '<button type="button" class="btn btn-success btn-sm" onclick="approveManpowerRequest(' + id + ')">Approve</button>';
        footer += '<button type="button" class="btn btn-danger btn-sm" onclick="declineManpowerRequest(' + id + ')">Decline</button>';
    }
    if (data.mp_status === 'approved') {
        footer += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleManpowerUpdateForm(\'edit\')">Request Edit</button>';
        footer += '<button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleManpowerUpdateForm(\'cancel\')">Request Cancel</button>';
    }
    $('#manpower-view-footer').html(footer);

    new bootstrap.Modal(document.getElementById('modal-manpower-view')).show();
}

let manpowerUpdateAction = null;

function toggleManpowerUpdateForm(action) {
    manpowerUpdateAction = action;
    $('#manpower-view-update-toggle').removeClass('d-none');
}

$('#btn-manpower-submit-update').click(async function () {
    const formData = new FormData();
    formData.append('action', manpowerUpdateAction);
    formData.append('reason', $('#manpower-update-reason').val());

    const response = await fetch("{{ url('recruitment/manpower') }}/" + currentManpowerId + "/request-update", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    const result = await response.json();
    if (result.success) {
        $('#modal-manpower-view').modal('hide');
        if (window.reloadManpowerList) window.reloadManpowerList();
    }
});

async function approveManpowerRequest(id) {
    if (!confirm('Approve this request?')) return;
    const formData = new FormData();
    formData.append('stat', 'approved');
    const response = await fetch("{{ url('recruitment/manpower') }}/" + id + "/status", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    const result = await response.json();
    if (result.success) {
        $('#modal-manpower-view').modal('hide');
        if (window.reloadManpowerList) window.reloadManpowerList();
    }
}

async function declineManpowerRequest(id) {
    const reason = prompt('Reason for declining:');
    if (reason === null) return;
    const formData = new FormData();
    formData.append('stat', 'declined');
    formData.append('reason', reason);
    const response = await fetch("{{ url('recruitment/manpower') }}/" + id + "/status", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    const result = await response.json();
    if (result.success) {
        $('#modal-manpower-view').modal('hide');
        if (window.reloadManpowerList) window.reloadManpowerList();
    }
}
</script>