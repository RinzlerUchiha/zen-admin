@foreach ($catList as $item)
    <div class="row mb-3 cat-item" data-cat="{{ $item->catstat_id }}">
        <span class="fs-5 fw-bold col-form-label col-form-label-sm col-md-3">{{ $item->cat_title }}</span>
        <span class="fs-5 col-form-label col-form-label-sm col-md">{{ $item->clearedby }}</span>
        <span class="fs-6 col-form-label col-form-label-sm col-md-auto"><span class="badge text-bg-{{ $item->catstat_stat == 'cleared' ? 'success' : ($item->catstat_stat == 'uncleared' ? 'secondary' : 'danger') }}">{{ strtoupper($item->catstat_stat) }}</span></span>
        @if ($item->requirements?->isNotEmpty())
            <div class="col-md-12">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>NA</th>
                            <th>Requirement</th>
                            <th>Date Verified</th>
                            <th>Verified By</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item->requirements as $r)
                            @if ($item->viewonly)
                                <tr class="{{ $r->catreq_required === 1 ? 'not-required' : '' }}">
                                    <td>{{ $r->catreq_required === 1 ? 'NA' : '' }}</td>
                                    <td style="max-width: 40%; width: 100%;">{{ $r->req_name }}</td>
                                    <td>{{ $r->catreq_dtcleared ?? '' }}</td>
                                    <td style="min-width: 150px;">{{ $r->clearedby ?? '' }}</td>
                                    <td>{!! nl2br(e($r->catreq_remarks ?? '')) !!}</td>
                                </tr>
                            @else
                                <tr class="cat-req-item" data-id="{{ $r->req_id }}">
                                    <td>
                                        <input class="cat-req-na" style="width: 20px; height: 20px;" type="checkbox" value="1" {{ $r->catreq_required === 1 ? 'checked' :'' }}>
                                    </td>
                                    <td style="max-width: 40%; width: 100%;">{{ $r->req_name }}</td>
                                    <td><input type="date" class="cat-req-verified-date" id="cat-req-verified-date-{{ $loop->parent->index.'-'.$loop->index }}" value="{{ $r->catreq_dtcleared ?? '' }}"></td>
                                    <td style="min-width: 150px;">
                                        <select class="form-control form-control-sm selectpicker cat-req-verified-by" id="cat-req-verified-by-{{ $loop->parent->index.'-'.$loop->index }}" data-width="auto" title="Select" data-live-search="true">
                                            @foreach ($employees->filter(fn($r2) => $r2->pers_empno == $item->catstat_emp || ($r->catreq_clearedby ?? '') == $r2->pers_empno || strpos($item->cat_checker, $r2->pers_empno) !== false) as $v)
                                                <option value="{{ $v->pers_empno }}" {{ ($r->catreq_clearedby ?? '') == $v->pers_empno || $loop->count == 1 ? 'selected' : '' }}>{{ $v->pers_lastname.trim(" ".($v->pers_suffix ?? '')).", ".$v->pers_firstname }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="cat-req-remarks" value="{{ $r->catreq_remarks ?? '' }}"></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                @if ($item->catstat_stat != 'cleared' && !$item->viewonly)
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sm btn-light px-3 border" data-action="save" onclick="checkRequirements(this)">Save</button>
                        @if ($user_empno == $item->catstat_emp)
                            <button class="btn btn-sm btn-primary ms-3" data-action="cleared" onclick="checkRequirements(this)">Cleared</button>
                            @if ($item->catstat_stat != 'uncleared')
                                <button class="btn btn-sm btn-outline-secondary ms-3" data-action="uncleared" onclick="checkRequirements(this)">Uncleared</button>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endforeach