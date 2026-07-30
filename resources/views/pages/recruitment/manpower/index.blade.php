@extends('layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Manpower Requests</h4>
        <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#modal-manpower-form">
            <i class="fa fa-plus"></i> New Request
        </button>
    </div>

    <ul class="nav nav-tabs" id="manpowerTab">
        <li class="nav-item"><button class="nav-link active" data-stat="draft">Draft <span class="badge bg-secondary" data-count="draft">–</span></button></li>
        <li class="nav-item"><button class="nav-link" data-stat="pending">Pending <span class="badge bg-secondary" data-count="pending">–</span></button></li>
        <li class="nav-item"><button class="nav-link" data-stat="approved">Approved <span class="badge bg-secondary" data-count="approved">–</span></button></li>
        <li class="nav-item"><button class="nav-link" data-stat="cancelled">Cancelled <span class="badge bg-secondary" data-count="cancelled">–</span></button></li>
        <li class="nav-item"><button class="nav-link" data-stat="declined">Declined <span class="badge bg-secondary" data-count="declined">–</span></button></li>
    </ul>

    <div id="manpower-list-container" class="mt-3"></div>
</div>

@include('pages.recruitment.manpower.form')
@include('pages.recruitment.manpower.view')

<script>
$(function () {
    let curStat = 'draft';

    function loadList(stat) {
        curStat = stat;
        $('#manpower-list-container').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i></div>');
        fetch("{{ route('recruitment.manpower.list', ':stat') }}".replace(':stat', stat))
            .then(r => r.text())
            .then(html => $('#manpower-list-container').html(html));
    }

    function loadCounts() {
        fetch("{{ route('recruitment.manpower.counts') }}")
            .then(r => r.json())
            .then(counts => {
                Object.keys(counts).forEach(stat => {
                    $('[data-count="' + stat + '"]').text(counts[stat]);
                });
            });
    }

    $('#manpowerTab .nav-link').click(function () {
        $('#manpowerTab .nav-link').removeClass('active');
        $(this).addClass('active');
        loadList($(this).data('stat'));
    });

    window.reloadManpowerList = () => { loadList(curStat); loadCounts(); };

    loadList('draft');
    loadCounts();
});
</script>
@stop