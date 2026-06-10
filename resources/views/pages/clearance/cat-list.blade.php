@foreach ($catList as $item)
    <div class="row mb-3">
        <label for="select-cat-{{ $loop->index }}" class="col-form-label col-form-label-sm col-md-4">{{ $item->cat_title }}</label>
        <div class="col-md">
            <input type="hidden" class="clr-cat" value="{{ $item->cat_id }}">
            <select class="form-control form-control-sm selectpicker clr-cat-checker" id="select-cat-{{ $loop->index }}" data-width="auto" title="Select" data-live-search="true">
                @foreach ($employees->filter(fn($r) => $r->ji_remarks == 'Active' || (!empty($item->catstat_emp) && $r->pers_empno == $item->catstat_emp) || strpos($item->cat_checker, $r->pers_empno) !== false) as $v)
                    <option data-company="{{ $v->jrec_company }}" value="{{ $v->pers_empno }}" {{ !empty($item->catstat_emp) && $v->pers_empno == $item->catstat_emp ? 'selected' : '' }}>{{ $v->pers_lastname.trim(" ".($v->pers_suffix ?? '')).", ".$v->pers_firstname }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endforeach