<div class="card text-bg-body mb-3">
    <div class="card-body p-2">
        <h6 class="card-title mb-0">For Exit Interview</h6>
        <div class="list-group list-group-flush border-top mt-1 overflow-y-auto" style="max-height: 150px;">
            @if ($list->count() > 0)
                @foreach ($list as $l)
                    <a href="{{ config('app.url') }}/outgoing?new={{ $l->pers_empno }}" target="_blank" class="p-1 list-group-item list-group-item-action text-reset d-flex">
                        <small class="fw-medium">{{ ucwords(trim($l->pers_lastname.', '.$l->pers_firstname)) }}</small>
                        <i class="ms-auto bi bi-chevron-double-right"></i>
                    </a>
                @endforeach
            @else
                <li class="p-1 list-group-item text-center">-</li>
            @endif
        </div>
    </div>
</div>