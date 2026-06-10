<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #event-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#holiday-image').on('change', function (e) {
            if(e.target.files.length > 0){
                const reader = new FileReader();
                reader.onload = function (e2) {
                const imgPreview = `<img src="${e2.target.result}" alt="Preview" style="max-width: 100%; border: 1px solid #ccc; border-radius: 5px;">`;
                    $('#preview-holiday-image').html(imgPreview);
                };
                reader.readAsDataURL(e.target.files[0]);
            }else{
                $('#preview-holiday-image').html('');
            }
        });

        $('#modal-holiday').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#holiday-id').val(btn.data('id') || '');
            $('#holiday-name').val(btn.data('holiday') || '');
            $('#holiday-date').val(btn.data('date') || '');
            $('#holiday-type').val(btn.data('type') || '');
            $('#holiday-scope').val(btn.data('scope').split(',') || '').selectpicker('refresh');
        });

        $('#form-holiday').submit(async function(e){
            e.preventDefault();

            $('#holiday-err').html("");
            
            let formData = new FormData();
            formData.append('id', $('#holiday-id').val());
            formData.append('holiday', $('#holiday-name').val());
            formData.append('date', $('#holiday-date').val());
            formData.append('type', $('#holiday-type').val());
            formData.append('scope', $('#holiday-scope').val().join(','));
            if($('#holiday-image')[0].files.length > 0){
                formData.append('file', $('#holiday-image')[0].files[0]);
            }

            let response = await fetch('/events/holiday/save', {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            });

            let result = await response.json();

            if (response.ok && result.success) {
                $('.modal').modal('hide');
                alert('Saved');
                load_holiday();
            } else {
                $('#holiday-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_holiday();
    });

    async function load_holiday() {
        $('#event-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/events/holiday/list/');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#event-list').html(html);

            $('#event-list > table').DataTable({
                // scrollX: '100%',
                scrollY: '55vh',
                scrollCollapse: true,
                lengthMenu: [50, 100, { label: 'All', value: -1 }],
                ordering: false
            });
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function remove_holiday(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/events/holiday/delete/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_holiday();
                } else {
                    alert('Failed remove to post');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>

<div class="container-fluid">
    <div class="d-flex">
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-holiday">New</button>
    </div>
    <div id="event-list"></div>
</div>

<div class="modal fade" id="modal-holiday" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-holiday-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-holiday-label">Holiday</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-holiday">
                <div class="modal-body">
                    <div class="row" id="holiday-err"></div>
                    <input type="hidden" id="holiday-id" value="">
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="holiday-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Date:</labe>
                        <div class="col-auto">
                            <input type="date" id="holiday-date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Type:</labe>
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="holiday-type" required>
                                <option value="Legal">Legal</option>
                                <option value="Special">Special</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Scope:</labe>
                        <div class="col-auto">
                            <select class="form-control form-control-sm selectpicker" id="holiday-scope" title="Select Area" data-live-search="true" multiple data-actions-box="true" required>
                                <option value="#all">All</option>
                                @foreach ($area as $a)
                                    <option value="{{ $a->Area_Code }}">{{ $a->Area_Code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="file" accept="image/*" class="form-control form-control-sm" id="holiday-image">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="preview-holiday-image"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-post-holiday">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>