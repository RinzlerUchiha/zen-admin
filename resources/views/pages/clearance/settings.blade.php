<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style>
    .bootstrap-select{
        max-width: 100% !important;
    }

    #clr-setting-company {
        max-width: fit-content;
    }

    #clr-setting-container * {
        font-size: 12px;
    }
</style>

<script>
    $(function(){
        $('.selectpicker').selectpicker('refresh');
        $('#clr-setting-company').change(function(){
            if(this.value){
                $('#btn-add-cat').show();
            }else{
                $('#btn-add-cat').hide();
            }
            loadCat();
        });

        $('#modal-clr-cat').on('show.bs.modal', function(e){
            let src = $(e.relatedTarget);

            $('#clr-cat-id').val(src.data('id'));
            $('#clr-cat-company-name').text($('#clr-setting-company option:selected').text());
            $('#clr-cat-title').val(src.data('title'));
            $('#clr-cat-desc').val(src.data('desc'));
            $('#clr-cat-priority').val(src.data('priority') || 1);
            $('#clr-cat-order').val(src.data('order') || 1);
            $('#clr-cat-status').val(src.data('stat') || 'active');
            $('#clr-cat-checker').val((src.data('checker') || '').split(',')).selectpicker('refresh');
        });

        $('#form-clr-cat').submit(async function(e){
            e.preventDefault();

            $('#clr-cat-err').html("");

            let formData = new FormData();
            formData.append('id', $('#clr-cat-id').val());
            formData.append('company', $('#clr-setting-company').val());
            formData.append('title', $('#clr-cat-title').val());
            formData.append('desc', $('#clr-cat-desc').val());
            formData.append('priority', $('#clr-cat-priority').val());
            formData.append('order', $('#clr-cat-order').val());
            formData.append('stat', $('#clr-cat-status').val());
            formData.append('checker', $('#clr-cat-checker').val().join(','));

            let response = await fetch('/clearance/set/cat', {
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
                loadCat();
            } else {
                $('#clr-cat-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        $('#modal-clr-cat-req').on('show.bs.modal', function(e){
            let src = $(e.relatedTarget);

            $('#clr-cat-req-id').val(src.data('id'));
            $('#clr-cat-req-catid').val(src.data('cat'));
            $('#clr-cat-req-name').val(src.data('name'));
            $('#clr-cat-req-status').val(src.data('stat') || 'active');
        });

        $('#form-clr-cat-req').submit(async function(e){
            e.preventDefault();

            $('#clr-cat-req-err').html("");

            let formData = new FormData();
            formData.append('id', $('#clr-cat-req-id').val());
            formData.append('cat', $('#clr-cat-req-catid').val());
            formData.append('name', $('#clr-cat-req-name').val());
            formData.append('stat', $('#clr-cat-req-status').val());

            let response = await fetch('/clearance/set/req', {
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
                
                if($('#clr-cat-req-id').val()){
                    let tr = $('#cat-item-' + $('#clr-cat-req-catid').val()).find('.tbl-cat-req tbody tr[data-id="' + $('#clr-cat-req-id').val() + '"]');
                    tr.data('id', $('#clr-cat-req-id').val());
                    tr.data('cat', $('#clr-cat-req-catid').val());
                    tr.data('name', $('#clr-cat-req-name').val());
                    tr.data('stat', $('#clr-cat-req-status').val());
                    tr.find('td').eq(0).text($('#clr-cat-req-name').val());
                    tr.find('td').eq(1).html('<span class="badge text-bg-' + ( $('#clr-cat-req-status').val() == 'active' ? 'success' : 'secondary') + '">' + $('#clr-cat-req-status option:selected').text() + '</span>');
                }else{
                    let tr  = $('<tr>', {
                        'data-bs-toggle': "modal",
                        'data-bs-target': "#modal-clr-cat-req",
                        'data-id': result.id,
                        'data-cat': $('#clr-cat-req-catid').val(),
                        'data-name': $('#clr-cat-req-name').val(),
                        'data-stat': $('#clr-cat-req-status').val()
                    });

                    tr.append('<td>' + $('#clr-cat-req-name').val() + '</td>');
                    tr.append('<td><span class="badge text-bg-' + ( $('#clr-cat-req-status').val() == 'active' ? 'success' : 'secondary') + '">' + $('#clr-cat-req-status option:selected').text() + '</span></td>');

                    $('#cat-item-' + $('#clr-cat-req-catid').val()).find('.tbl-cat-req tbody').append(tr);
                }
                
            } else {
                $('#clr-cat-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });
    });

    async function loadCat() {
        try {
            $('#clr-setting-container').html('Loading...');
            // Make the fetch request to the Laravel controller
            const response = await fetch('/clearance/settings/' + $('#clr-setting-company').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#clr-setting-container').html(html);
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>

<div class="row mb-3">
    <div class="col-auto">
        <div class="input-group">
            <span class="input-group-text">Company</span>
            <select class="form-select" id="clr-setting-company">
                <option value selected disabled>-</option>
                @foreach ($companyList as $item)
                    <option class="text-start" value="{{ $item->C_Code }}">{{ $item->C_Name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-clr-cat" id="btn-add-cat" style="display: none;"><i class="fa fa-plus"></i> Add Category</button>
    </div>
</div>
<div class="container-fluid" id="clr-setting-container"></div>

<div class="modal fade" id="modal-clr-cat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
aria-labelledby="modal-clr-cat-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-clr-cat-label">Clearance Category</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-clr-cat">
                <div class="modal-body">
                    <div class="row" id="clr-cat-err"></div>
                    <input type="hidden" id="clr-cat-id" value="">
                    <div class="row mb-3">
                        <span class="col-form-label col-form-label-sm col-md-3">Company:</span>
                        <span class="col-form-label col-form-label-sm col-md" id="clr-cat-company-name">-</span>
                    </div>
                    <div class="row mb-3">
                        <label class="col-form-label col-form-label-sm col-md-3">Title:</label>
                        <div class="col-md">
                            <input type="text" class="form-control form-control-sm" id="clr-cat-title">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-form-label col-form-label-sm col-md-3">Description:</label>
                        <div class="col-md">
                            <textarea class="form-control form-control-sm" id="clr-cat-desc"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-md-6">Priority:</label>
                                <div class="col-md-6">
                                    <input type="number" min="1" class="form-control form-control-sm" id="clr-cat-priority">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row mb-3">
                                <label class="col-form-label col-form-label-sm col-md-6 text-md-end">Order:</label>
                                <div class="col-md-6">
                                    <input type="number" min="1" class="form-control form-control-sm" id="clr-cat-order">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-form-label col-form-label-sm col-md-3">Status:</label>
                        <div class="col-md-auto">
                            <select class="form-select form-select-sm" id="clr-cat-status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-form-label col-md-12">Requirement Checkers:</label>
                        <div class="col-md-12">
                            <select class="form-control form-control-sm selectpicker" data-width="auto" id="clr-cat-checker" title="Select Requirement Checkers" data-live-search="true" multiple data-actions-box="true" required>
                                @foreach ($employees as $v)
                                    <option data-company="{{ $v['jrec_company'] }}" value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-light">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-clr-cat-req" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
aria-labelledby="modal-clr-cat-req-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-clr-cat-req-label">Category Requirement</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-clr-cat-req">
                <div class="modal-body">
                    <div class="row" id="clr-cat-req-err"></div>
                    <input type="hidden" id="clr-cat-req-id" value="">
                    <input type="hidden" id="clr-cat-req-catid" value="">
                    <div class="row mb-3">
                        <label class="col-form-label col-form-label-sm col-md-3">Name:</label>
                        <div class="col-md">
                            <input type="text" class="form-control form-control-sm" id="clr-cat-req-name">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-form-label col-form-label-sm col-md-3">Status:</label>
                        <div class="col-md-auto">
                            <select class="form-select form-select-sm" id="clr-cat-req-status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-light">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>