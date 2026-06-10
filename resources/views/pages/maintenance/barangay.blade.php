<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #barangay-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#modal-barangay').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#barangay-id').val(btn.data('id'));
            $('#barangay-city').val(btn.data('city'));
            $('#barangay-name').val(btn.data('name'));
            $('#barangay-status').val(btn.data('status') ?? 1);
        });

        $('#form-barangay').submit(async function(e){
            e.preventDefault();

            $('#barangay-err').html("");
            
            let formData = new FormData();
            formData.append('id', $('#barangay-id').val());
            formData.append('city', $('#barangay-city').val());
            formData.append('name', $('#barangay-name').val());
            formData.append('status', $('#barangay-status').val());

            let response = await fetch('/maintenance/barangay/save', {
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
                load_barangay();
            } else {
                $('#barangay-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_barangay();
    });

    async function load_barangay() {
        $('#barangay-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/barangay/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#barangay-list').html(html);

            $('#barangay-list > table').DataTable({
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

    async function remove_barangay(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/barangay/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_barangay();
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
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-barangay">New</button>
    </div>
    <div id="barangay-list"></div>
</div>

<div class="modal fade" id="modal-barangay" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-barangay-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-barangay-label">Barangay</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-barangay">
                <div class="modal-body">
                    <div class="row" id="barangay-err"></div>
                    <input type="hidden" id="barangay-id" value="">
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">City:</labe>
                        <div class="col-8">
                            <select class="form-select form-select-sm" id="barangay-city" required>
                                @foreach ($city as $c)
                                    <option value="{{ $c->ct_id }}">{{ $c->ct_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="barangay-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Status:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="barangay-status" required>
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