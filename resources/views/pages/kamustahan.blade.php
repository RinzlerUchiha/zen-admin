@extends('layouts.layout')

@section('content')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>
    <style>
        #emplist,
        #emp-kamustahan-list {
            font-size: 12px;
        }

        .breadcrumb-chevron {
            --bs-breadcrumb-divider: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            gap: .5rem;
        }
        .breadcrumb-chevron .breadcrumb-item {
            display: flex;
            gap: inherit;
            align-items: center;
            padding-left: 0;
            line-height: 1;
        }
        .breadcrumb-chevron .breadcrumb-item::before {
            gap: inherit;
            float: none;
            width: 1rem;
            height: 1rem;
        }
    </style>

    <script>
        $(function(){
            let table = $('#emplist > table').DataTable({
                scrollY: '70vh',
                scrollCollapse: true,
                lengthMenu: [50, 100, { label: 'All', value: -1 }],
                ordering: false
            });
        });
    </script>

    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-chevron">
                @if(empty($empno))
                    <li class="breadcrumb-item active" aria-current="page">Kamustahan</li>
                @else
                    <li class="breadcrumb-item"><a class="link-body-emphasis fw-semibold text-decoration-none" href="{{ '/kamustahan' }}">Kamustahan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $empname }} <a href="{{ '/kamustahan/info/?empno=' . $empno }}" class="btn btn-outline-primary btn-sm float-end"><i class="fa fa-plus"></i></a></li>
                @endif
            </ol>
        </nav>

        <div id="emplist">
            @if(!empty($empno))
                {{-- <div class="d-flex justify-content-end">
                    <a href="{{ '/kamustahan' }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i></a>
                    
                    <a href="{{ '/kamustahan/info/?empno=' . $empno }}" class="btn btn-outline-primary"><i class="fa fa-plus"></i></a>
                </div> --}}
                <table class="table table-sm table-bordered table-striped table-hover">
                <thead>
                <tr>
                <th class="text-start">Date Time</th>
                <th>Employee</th>
                <th>Interviewer</th>
                </tr>
                </thead>
                
                <tbody>
                    @foreach ($list as $v)
                        <tr ondblclick="window.location = '{{ '/kamustahan/info/' . $v->ekmst_id }}'">
                        <td class="text-start">{{ date('M d, Y h:i A', strtotime($v->ekmst_intvwdate)) }}</td>
                        <td>{{ $v->empname }}</td>
                        <td>{{ $v->interviewer_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            @else
                <table class="table table-sm table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Emp #</th>
                            <th>Company</th>
                            <th>Department</th>
                            <th>Name</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emplist as $v)
                            <tr ondblclick="window.location = '/kamustahan/list/{{ $v->pers_empno }}'">
                                <td>{{ $v->pers_empno }}</td>
                                <td>{{ $v->C_Name }}</td>
                                <td>{{ $v->Dept_Name }}</td>
                                <td>{{ trim(ucwords($v->pers_lastname . ', ' . $v->pers_firstname)) }}</td>
                                <td>{{ $v->jd_title }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@stop