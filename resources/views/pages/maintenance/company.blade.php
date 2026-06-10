<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    #company-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#modal-company').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#company-code').val(btn.data('code')).prop('disabled', btn.data('code') ? true : false);
            $('#company-name').val(btn.data('name'));
            $('#company-description').val(btn.data('description'));
            $('#company-tin').val(btn.data('tin'));
            $('#company-sss').val(btn.data('sss'));
            $('#company-phic').val(btn.data('phic'));
            $('#company-hdmf').val(btn.data('hdmf'));
            $('#company-address').val(btn.data('address'));
            $('#company-owned').val(btn.data('owned') || 'True');
            $('#company-status').val(btn.data('status') || 'active');
        });

        $('#form-company').submit(async function(e){
            e.preventDefault();

            $('#company-err').html("");
            
            let formData = new FormData();
            formData.append('code', $('#company-code').val());
            formData.append('name', $('#company-name').val());
            formData.append('description', $('#company-description').val());
            formData.append('tin', $('#company-tin').val());
            formData.append('sss', $('#company-sss').val());
            formData.append('phic', $('#company-phic').val());
            formData.append('hdmf', $('#company-hdmf').val());
            formData.append('address', $('#company-address').val());
            formData.append('owned', $('#company-owned').val());
            formData.append('status', $('#company-status').val());

            let response = await fetch('/maintenance/company/save', {
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
                load_company();
            } else {
                $('#company-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_company();
    });

    async function load_company() {
        $('#company-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/company/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#company-list').html(html);

            $('#company-list > table').DataTable({
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

    async function remove_company(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/company/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_company();
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
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-company">New</button>
    </div>
    <div id="company-list"></div>
</div>

<div class="modal fade" id="modal-company" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-company-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-company-label">Company</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-company">
                <div class="modal-body">
                    <div class="row" id="company-err"></div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Code:</labe>
                        <div class="col-5">
                            <input type="text" id="company-code" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Name:</labe>
                        <div class="col-8">
                            <input type="text" id="company-name" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Description:</labe>
                        <div class="col-8">
                            <input type="text" id="company-description" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">TIN:</labe>
                        <div class="col-8">
                            <input type="text" id="company-tin" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">SSS:</labe>
                        <div class="col-8">
                            <input type="text" id="company-sss" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">PHIC:</labe>
                        <div class="col-8">
                            <input type="text" id="company-phic" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">HDMF:</labe>
                        <div class="col-8">
                            <input type="text" id="company-hdmf" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Address:</labe>
                        <div class="col-8">
                            <input type="text" id="company-address" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Owned:</labe>
                        <div class="col-8">
                            <select class="form-select form-select-sm" id="company-owned" required>
                                <option value="True">Yes</option>
                                <option value="False">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Status:</labe>
                        <div class="col-5">
                            <select class="form-select form-select-sm" id="company-status" required>
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