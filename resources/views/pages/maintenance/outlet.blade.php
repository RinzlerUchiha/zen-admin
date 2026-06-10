<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #outlet-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#modal-outlet').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#outlet-code').val(btn.data('code')).prop('disabled', btn.data('code') ? true : false);
            $('#outlet-name').val(btn.data('name'));
            $('#outlet-area').val(btn.data('area'));
            $('#outlet-opening-date').val(btn.data('openingdt'));
            $('#outlet-closing-date').val(btn.data('closingdt'));
            $('#outlet-size').val(btn.data('size'));
            $('#outlet-type').val(btn.data('type'));
            $('#outlet-status').val(btn.data('status') || 'active');
        });

        $('#form-outlet').submit(async function(e){
            e.preventDefault();

            $('#outlet-err').html("");
            
            let formData = new FormData();
            formData.append('code', $('#outlet-code').val());
            formData.append('name', $('#outlet-name').val());
            formData.append('area', $('#outlet-area').val());
            formData.append('openingdt', $('#outlet-opening-date').val());
            formData.append('closingdt', $('#outlet-closing-date').val());
            formData.append('size', $('#outlet-size').val());
            formData.append('type', $('#outlet-type').val());
            formData.append('status', $('#outlet-status').val());

            let response = await fetch('/maintenance/outlet/save', {
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
                load_outlet();
            } else {
                $('#outlet-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_outlet();
    });

    async function load_outlet() {
        $('#outlet-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/outlet/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#outlet-list').html(html);

            $('#outlet-list > table').DataTable({
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

    async function remove_outlet(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/outlet/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_outlet();
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
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-outlet">New</button>
    </div>
    <div id="outlet-list"></div>
</div>

<div class="modal fade" id="modal-outlet" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-outlet-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-outlet-label">Outlet</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-outlet">
                <div class="modal-body">
                    <div class="row" id="outlet-err"></div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Code:</labe>
                        <div class="col-5">
                            <input type="text" id="outlet-code" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="outlet-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Area:</labe>
                        <div class="col-8">
                            <select class="form-select form-select-sm" id="outlet-area" required>
                                @foreach ($area as $a)
                                    <option value="{{ $a->Area_Code }}">({{ $a->Area_Code }}) {{ $a->Area_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Opening Date:</labe>
                        <div class="col-5">
                            <input type="date" id="outlet-opening-date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Closing Date:</labe>
                        <div class="col-5">
                            <input type="date" id="outlet-closing-date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Type:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="outlet-type" required>
                                <option value="boutique">Boutique</option>
                                <option value="kiosk">Kiosk</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Size:</labe>
                        <div class="col-5">
                            <input type="number" id="outlet-size" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Status:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="outlet-status" required>
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