<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #province-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#modal-province').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#province-id').val(btn.data('id'));
            $('#province-code').val(btn.data('code'));
            $('#province-name').val(btn.data('name'));
            $('#province-status').val(btn.data('status') ?? 1);
        });

        $('#form-province').submit(async function(e){
            e.preventDefault();

            $('#province-err').html("");
            
            let formData = new FormData();
            formData.append('id', $('#province-id').val());
            formData.append('code', $('#province-code').val());
            formData.append('name', $('#province-name').val());
            formData.append('status', $('#province-status').val());

            let response = await fetch('/maintenance/province/save', {
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
                load_province();
            } else {
                $('#province-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_province();
    });

    async function load_province() {
        $('#province-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/province/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#province-list').html(html);

            $('#province-list > table').DataTable({
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

    async function remove_province(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/province/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_province();
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
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-province">New</button>
    </div>
    <div id="province-list"></div>
</div>

<div class="modal fade" id="modal-province" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-province-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-province-label">Province</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-province">
                <div class="modal-body">
                    <div class="row" id="province-err"></div>
                    <input type="hidden" id="province-id" value="">
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Code:</labe>
                        <div class="col-5">
                            <input type="text" id="province-code" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="province-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Status:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="province-status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
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