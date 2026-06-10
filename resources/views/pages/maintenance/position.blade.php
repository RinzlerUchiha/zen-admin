<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #position-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#modal-position').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#position-code').val(btn.data('code')).prop('disabled', btn.data('code') ? true : false);
            $('#position-name').val(btn.data('name'));
            $('#position-summary').val(btn.data('summary'));
            $('#position-duties').val(btn.data('duties'));
            $('#position-specification').val(btn.data('specification'));
            $('#position-status').val(btn.data('status') || 'active');
        });

        $('#form-position').submit(async function(e){
            e.preventDefault();

            $('#position-err').html("");
            
            let formData = new FormData();
            formData.append('code', $('#position-code').val());
            formData.append('name', $('#position-name').val());
            formData.append('summary', $('#position-summary').val());
            formData.append('duties', $('#position-duties').val());
            formData.append('specification', $('#position-specification').val());
            formData.append('status', $('#position-status').val());

            let response = await fetch('/maintenance/position/save', {
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
                load_position();
            } else {
                $('#position-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_position();
    });

    async function load_position() {
        $('#position-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/position/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#position-list').html(html);

            $('#position-list > table').DataTable({
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

    async function remove_position(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/position/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_position();
                } else {
                    alert('Failed remove');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>

<div class="container-fluid">
    <div class="d-flex mb-1">
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-position">New</button>
    </div>
    <div id="position-list"></div>
</div>

<div class="modal fade" id="modal-position" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-position-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-position-label">Position</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-position">
                <div class="modal-body">
                    <div class="row" id="position-err"></div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Code:</labe>
                        <div class="col-5">
                            <input type="text" id="position-code" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="position-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Summary:</labe>
                        <div class="col-8">
                            <input type="text" id="position-summary" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Duties:</labe>
                        <div class="col-8">
                            <input type="text" id="position-duties" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Specification:</labe>
                        <div class="col-8">
                            <input type="text" id="position-specification" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Status:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="position-status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>