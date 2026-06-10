@foreach ($catList as $cat)
    <div class="card mb-3" id="cat-item-{{ $cat->cat_id }}">
        <div class="row g-0">
            <div class="col-lg">
                <div class="card-body">
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-3">Title:</span>
                        <span class="col-form-label col-form-label-sm col-md clr-cat-title">{{ $cat->cat_title }}</span>
                    </div>
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-3">Description:</span>
                        <span class="col-form-label col-form-label-sm col-md clr-cat-desc">{{ $cat->cat_desc }}</span>
                    </div>
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-3">Priority:</span>
                        <span class="col-form-label col-form-label-sm col-md clr-cat-priority">{{ $cat->cat_priority }}</span>
                    </div>
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-3">Order:</span>
                        <span class="col-form-label col-form-label-sm col-md clr-cat-order">{{ $cat->cat_order }}</span>
                    </div>
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-3">Status:</span>
                        <span class="col-form-label col-form-label-sm col-md clr-cat-status"><span class="badge text-bg-{{ $cat->cat_status == 'active' ? 'success' : 'secondary' }}">{{ ucwords($cat->cat_status) }}</span></span>
                    </div>
                    <div class="row mb-2">
                        <span class="col-form-label col-form-label-sm col-md-12">Requirements Checker:</span>
                        <div class="offset-md-3 col-md clr-cat-checker-list">
                            @foreach ($cat->cat_checker_names as $chk)
                                <span class="d-block">- {{ $chk }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-sm btn-light border btn-edit-cat" 
                            data-bs-toggle="modal" data-bs-target="#modal-clr-cat"
                            data-id="{{ $cat->cat_id }}"
                            data-title="{{ $cat->cat_title }}"
                            data-desc="{{ $cat->cat_desc }}"
                            data-priority="{{ $cat->cat_priority }}"
                            data-order="{{ $cat->cat_order }}"
                            data-checker="{{ $cat->cat_checker }}"
                            data-stat="{{ $cat->cat_status }}"><i class="fa fa-edit"></i></button>
                            {{-- <button class="btn btn-sm btn-danger border btn-del-cat"><i class="fa fa-trash"></i></button> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg">
                <div class="card-body">
                    <table class="table table-sm table-striped tbl-cat-req">
                        <thead>
                            <tr>
                                <th>Requirements</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cat->requirement as $r)
                                <tr data-bs-toggle="modal" data-bs-target="#modal-clr-cat-req"
                                    data-id="{{ $r->req_id }}"
                                    data-cat="{{ $r->req_cat }}"
                                    data-name="{{ $r->req_name }}"
                                    data-stat="{{ $r->req_status }}">
                                    <td>{{ $r->req_name }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $r->req_status == 'active' ? 'success' : 'secondary' }}">{{ ucwords($r->req_status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button class="btn btn-sm btn-primary border" data-bs-toggle="modal" data-bs-target="#modal-clr-cat-req" data-cat="{{ $cat->cat_id }}"><i class="fa fa-plus"></i> Add Requirement</button>
                </div>
            </div>
        </div>
    </div>
@endforeach




{{-- <div class="card">
    <div class="row g-0">
        <div class="col-lg">
            <div class="card-body">
                <div class="row mb-2">
                    <label for="" class="col-form-label col-form-label-sm col-md-3">Title:</label>
                    <div class="col-md">
                        <input type="text" class="form-control form-control-sm clr-cat-title">
                    </div>
                </div>
                <div class="row mb-2">
                    <label for="" class="col-form-label col-form-label-sm col-md-3">Description:</label>
                    <div class="col-md">
                        <textarea class="form-control form-control-sm clr-cat-desc"></textarea>
                    </div>
                </div>
                <div class="row mb-2">
                    <label for="" class="col-form-label col-form-label-sm col-md-3">Priority:</label>
                    <div class="col-md-3">
                        <input type="number" class="form-control form-control-sm clr-cat-priority">
                    </div>

                    <label for="" class="col-form-label col-form-label-sm col-md text-end">Order:</label>
                    <div class="col-md-3">
                        <input type="number" class="form-control form-control-sm clr-cat-order">
                    </div>
                </div>
                <div class="row mb-2">
                    <label for="" class="col-form-label col-form-label-sm col-md-3">Requirements Checker:</label>
                    <div class="col-md">
                        <select class="form-control form-control-sm selectpicker" data-width="auto" data-style="border" id="clr-cat-checker-select" title="Select to add" data-live-search="true" required>
                            @foreach ($employees as $v)
                                <option data-company="{{ $v['jrec_company'] }}" value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                            @endforeach
                        </select>
                        <div class="row my-2">
                            <div class="col-md clr-cat-checker-list">
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                                <span class="d-block">- Bongcawel, Meljoy</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-sm btn-light border btn-edit-cat"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-sm btn-primary btn-save-cat">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg">
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Requirements</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <td></td>
                        <td></td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> --}}