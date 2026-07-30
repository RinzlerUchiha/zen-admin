<div class="modal fade" id="modal-manpower-form" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manpower Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-manpower">
                <div class="modal-body">
                    <div id="manpower-form-err"></div>
                    <input type="hidden" id="manpower-id">

                    <h6>Replacement</h6>
                    <table class="table table-sm" id="manpower-replacement-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th style="width:90px;">Count</th>
                                <th>Reason</th>
                                <th style="width:150px;">Date Needed</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm manpower-position">
                                        <option value="" disabled selected>-</option>
                                        @foreach($position as $p)
                                            <option value="{{ $p->jd_code }}">{{ $p->jd_title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm manpower-count" min="1" value="1"></td>
                                <td>
                                    <select class="form-select form-select-sm manpower-reason">
                                        <option value="Resignation">Resignation</option>
                                        <option value="Terminated w/ cause">Terminated w/ cause</option>
                                        <option value="End of contract">End of contract</option>
                                    </select>
                                </td>
                                <td><input type="date" class="form-control form-control-sm manpower-date"></td>
                                <td><button type="button" class="btn btn-sm btn-danger btn-del-row"><i class="fa fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btn-add-replacement">
                        <i class="fa fa-plus"></i> Add Replacement Position
                    </button>

                    <h6>Additional</h6>
                    <table class="table table-sm" id="manpower-additional-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th style="width:90px;">Count</th>
                                <th>Reason</th>
                                <th style="width:150px;">Date Needed</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm manpower-position">
                                        <option value="" disabled selected>-</option>
                                        @foreach($position as $p)
                                            <option value="{{ $p->jd_code }}">{{ $p->jd_title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm manpower-count" min="1" value="1"></td>
                                <td><input type="text" class="form-control form-control-sm manpower-reason-text"></td>
                                <td><input type="date" class="form-control form-control-sm manpower-date"></td>
                                <td><button type="button" class="btn btn-sm btn-danger btn-del-row"><i class="fa fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btn-add-additional">
                        <i class="fa fa-plus"></i> Add Additional Position
                    </button>

                    <div class="mb-3">
                        <label class="form-label">Non-negotiable</label>
                        <textarea id="manpower-nonnegotiable" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="btn-manpower-draft">Save Draft</button>
                    <button type="submit" class="btn btn-primary" id="btn-manpower-post">Post Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    const tplReplacement = $('#manpower-replacement-table tbody tr').first().clone();
    const tplAdditional = $('#manpower-additional-table tbody tr').first().clone();

    $('#btn-add-replacement').click(function () {
        $('#manpower-replacement-table tbody').append(tplReplacement.clone());
    });
    $('#btn-add-additional').click(function () {
        $('#manpower-additional-table tbody').append(tplAdditional.clone());
    });
    $(document).on('click', '.btn-del-row', function () {
        $(this).closest('tr').remove();
    });

    $('#modal-manpower-form').on('show.bs.modal', function (e) {
        if (!$(e.relatedTarget).is('[data-bs-target="#modal-manpower-form"]')) return;
        $('#form-manpower')[0].reset();
        $('#manpower-id').val('');
        $('#manpower-replacement-table tbody').html(tplReplacement.clone());
        $('#manpower-additional-table tbody').html(tplAdditional.clone());
    });

    function collectRows(tableSelector, reasonClass) {
        const rows = [];
        $(tableSelector + ' tbody tr').each(function () {
            const position = $(this).find('.manpower-position').val();
            if (!position) return;
            rows.push({
                position: position,
                count: $(this).find('.manpower-count').val(),
                reason: $(this).find('.' + reasonClass).val(),
                date: $(this).find('.manpower-date').val(),
                applicants_csv: ''
            });
        });
        return rows;
    }

    $('#btn-manpower-draft, #btn-manpower-post').click(function () {
        $('#manpower-submit-mode').remove();
        $('<input>').attr({ type: 'hidden', id: 'manpower-submit-mode' })
            .val(this.id === 'btn-manpower-draft' ? 'draft' : 'pending')
            .appendTo('#form-manpower');
        $('#form-manpower').trigger('submit');
    });

    $('#form-manpower').submit(async function (e) {
        e.preventDefault();
        $('#manpower-form-err').html('');

        const replacement = collectRows('#manpower-replacement-table', 'manpower-reason');
        const additional = collectRows('#manpower-additional-table', 'manpower-reason-text');
        const submitMode = $('#manpower-submit-mode').val() || 'draft';

        if (submitMode === 'pending' && replacement.length === 0 && additional.length === 0) {
            $('#manpower-form-err').html('<p class="text-danger">Add at least one position before posting.</p>');
            return;
        }

        let formData = new FormData();
        formData.append('id', $('#manpower-id').val());
        formData.append('replacement', JSON.stringify(replacement));
        formData.append('additional', JSON.stringify(additional));
        formData.append('nonnegotiable', $('#manpower-nonnegotiable').val());
        formData.append('submit_mode', submitMode);

        const response = await fetch("{{ route('recruitment.manpower.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        const result = await response.json();
        if (result.success) {
            $('#modal-manpower-form').modal('hide');
            if (window.reloadManpowerList) window.reloadManpowerList();
        } else {
            $('#manpower-form-err').html('<p class="text-danger">Error: ' + result.error + '</p>');
        }
    });
});

async function removeManpowerRequest(id) {
    if (!confirm('Are you sure?')) return;
    const response = await fetch("{{ url('recruitment/manpower') }}/" + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    const result = await response.json();
    if (result.success && window.reloadManpowerList) window.reloadManpowerList();
}
</script>