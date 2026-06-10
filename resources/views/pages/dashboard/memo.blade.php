<div class="card text-bg-body mb-3">
    <div class="card-body p-2">
        <h6 class="card-title mb-0">Memo
            <button class="ms-1 btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-memo" style="font-size: 10px;"><i class="fa fa-plus"></i></button>
        </h6>
        <div class="list-group list-group-flush border-top mt-1 overflow-y-auto cursor-pointer" style="max-height: 150px;">
            @if ($list->count() > 0)
                @foreach ($list as $l)
                    <li class="p-1 list-group-item list-group-item-action text-reset d-flex" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modal-view-memo" 
                    data-id="{{ $l->memo_id }}"
                    data-subject="{{ $l->memo_subject }}"
                    data-file="{{ config('app.url') }}/file/get/memo/{{ $l->memo_pdf }}">
                        <div class="d-block">
                            <small class="fw-medium d-block mb-1">{{ $l->memo_no }}</small>
                            <p class="m-0 small lh-sm">{{ $l->memo_subject }}</p>
                        </div>
                        @if(!$l->read_id)
                            <span class="ms-auto badge text-bg-danger align-self-start">Unread</span>
                        @endif
                    </li>
                @endforeach
            @else
                <li class="p-1 list-group-item text-center">-</li>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modal-view-memo" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-memo-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-memo-label">Memo</h1>
                <button type="button" class="btn-close mb-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-memo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-memo-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-memo-label">Memo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-memo">
                <div class="modal-body">
                    <div class="row" id="memo-err"></div>
                    <input type="hidden" id="memo-id" value="">
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-2">Recipient:</labe>
                        <div class="col-10">
                            <div class="d-flex" style="max-width: 100%;">
                                <select id="memo-recipient-type" class="form-select form-select-sm w-auto" required>
                                    <option value="All">All</option>
                                    <option value="Company">Company</option>
                                    <option value="Department">Department</option>
                                    <option value="Area">Area</option>
                                    <option value="Outlet">Outlet</option>
                                    <option value="Employee">Employee</option>
                                </select>

                                <select id="memo-recipient-company" class="memo-recipient d-none ms-1 form-control form-control-sm selectpicker" title="Company" data-live-search="true" multiple data-actions-box="true">
                                    @foreach($companyList as $k => $v)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>

                                <select id="memo-recipient-department" class="memo-recipient d-none ms-1 form-control form-control-sm selectpicker" title="Department" data-live-search="true" multiple data-actions-box="true">
                                    @foreach($departmentList as $k => $v)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>

                                <select id="memo-recipient-area" class="memo-recipient d-none ms-1 form-control form-control-sm selectpicker" title="Area" data-live-search="true" multiple data-actions-box="true">
                                    @foreach($areaList as $k => $v)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>

                                <select id="memo-recipient-outlet" class="memo-recipient d-none ms-1 form-control form-control-sm selectpicker" title="Outlet" data-live-search="true" multiple data-actions-box="true">
                                    @foreach($outletList as $k => $v)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>

                                <select id="memo-recipient-employee" class="memo-recipient d-none ms-1 form-control form-control-sm selectpicker" title="Employee" data-live-search="true" multiple data-actions-box="true">
                                    @foreach($employeeList as $k => $v)
                                        <option value="{{ $k }}">{{ ucwords(trim($v['pers_lastname'].', '.$v['pers_firstname'])) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-2">Subject:</labe>
                        <div class="col-10">
                            <input id="memo-subject" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="file" accept="application/pdf" class="form-control form-control-sm" id="memo-file">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="preview-memo-file"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-post-memo">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('#memo-file').on('change', function (e) {
            if(e.target.files.length > 0){
                const reader = new FileReader();
                reader.onload = function (e2) {
                    const imgPreview = `<embed src="${e2.target.result}" alt="Preview" style="width: 100%; border: 1px solid #ccc; border-radius: 5px; height: 35vh;"/>`;
                    $('#preview-memo-file').html(imgPreview);
                };
                reader.readAsDataURL(e.target.files[0]);
            }else{
                $('#preview-memo-file').html('');
            }
        });

        $('#memo-recipient-type').change(function(){
            $('#memo-recipient-company, #memo-recipient-department, #memo-recipient-area, #memo-recipient-outlet, #memo-recipient-employee').addClass('d-none');
            $('#form-memo .bootstrap-select').addClass('d-none');
            switch (this.value) {
                case 'Company':
                    $('#memo-recipient-company').removeClass('d-none');
                    $('#memo-recipient-company').parents('.bootstrap-select').removeClass('d-none');
                    break;

                case 'Department':
                    $('#memo-recipient-department').removeClass('d-none');
                    $('#memo-recipient-department').parents('.bootstrap-select').removeClass('d-none');
                    break;

                case 'Area':
                    $('#memo-recipient-area').removeClass('d-none');
                    $('#memo-recipient-area').parents('.bootstrap-select').removeClass('d-none');
                    break;

                case 'Outlet':
                    $('#memo-recipient-outlet').removeClass('d-none');
                    $('#memo-recipient-outlet').parents('.bootstrap-select').removeClass('d-none');
                    break;

                case 'Employee':
                    $('#memo-recipient-employee').removeClass('d-none');
                    $('#memo-recipient-employee').parents('.bootstrap-select').removeClass('d-none');
                    break;
            
                default:
                    break;
            }

            $('#form-memo .selectpicker').selectpicker('refresh');
        });
        $('#modal-memo').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#memo-id').val(btn.data('id'));
            $('#memo-subject').val(btn.data('subject'));
            $('#memo-recipient-type').val(btn.data('recipient-type') || 'All');
            $('#memo-recipient-company').val((btn.data('company') || '').split(','));
            $('#memo-recipient-department').val((btn.data('department') || '').split(','));
            $('#memo-recipient-area').val((btn.data('area') || '').split(','));
            $('#memo-recipient-outlet').val((btn.data('outlet') || '').split(','));
            $('#memo-recipient-employee').val((btn.data('employee') || '').split(','));
            $('#memo-recipient-type').trigger('change');

            $('#form-memo .selectpicker').selectpicker('refresh');
        });

        $('#form-memo').submit(async function(e){
            e.preventDefault();

            $('#memo-err').html("");
            
            let formData = new FormData();
            formData.append('id', $('#memo-id').val());
            formData.append('subject', $('#memo-subject').val());
            formData.append('recipient-type', $('#memo-recipient-type').val());
            formData.append('recipient-list', ($('select.memo-recipient:visible').val() || ['All']).join(','));
            if($('#memo-file')[0].files.length > 0){
                formData.append('file', $('#memo-file')[0].files[0]);
            }
            
            let response = await fetch('/memo/save', {
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
                fetchData('/dashboard/memo')
                .then(data => {
                    if(data){
                        $('#memo-area').html(data);
                    }
                });
            } else {
                $('#memo-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        $('#modal-view-memo').on('shown.bs.modal', async function(e){
            const btn = $(e.relatedTarget);
            
            $('#modal-view-memo .modal-body').html(`<embed src="${btn.data('file')}" alt="Preview" style="width: 100%; border: 1px solid #ccc; border-radius: 5px; height: 70vh;"/>`);

            const response = await fetch('/memo/read/' + btn.data('id'), {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            });

            const result = await response.json();

            $('#modal-view-memo .modal-title').html(btn.data('subject'));
        });
    });
</script>