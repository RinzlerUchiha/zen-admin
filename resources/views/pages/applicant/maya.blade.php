@extends('pages.applicant.profile')

@section('profile_content')

    <style>
        #form-maya {
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
            font-size: 15px;
        }

        #maya-answers >.card:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #maya-answers >.card:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        td .fs-custom {
            font-size: 20px;
            line-height: 20px;
        }
    </style>

    <div class="row mb-5" style="font-size: 12px;">
        <div class="col-md-auto">
            <table class="table table-bordered text-center w-auto">
                <tr>
                    @foreach ($mayaResult['totalPerSet'] ?? [] as $i => $item)
                        <th>Set {{ strtoupper($i) }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($mayaResult['totalPerSet'] ?? [] as $i => $item)
                        <td>{{ $item }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td colspan="{{ count($mayaResult['totalPerSet'] ?? []) }}">{{ $mayaResult['totalOverallSet'] ?? '' }}</td>
                </tr>
                <tr>
                    <td colspan="{{ count($mayaResult['totalPerSet'] ?? []) }}">Percentile: {{ $mayaResult['percentile'] ?? '' }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md">
            <table class="table table-bordered text-center">
                <tr>
                    <th colspan="10">Raven's Progressive Matrices</th>
                </tr>
                <tr>
                    <th></th>
                    <th colspan="3" style="text-align:center;">LOW</th>
                    <th colspan="3" style="text-align:center;">AVERAGE</th>
                    <th colspan="3" style="text-align:center;">HIGH</th>
                </tr>
                <tr>
                    <th>Percentile</th>
                    <th style="text-align:center;">1-3</th>
                    <th style="text-align:center;">4-9</th>
                    <th style="text-align:center;">10-23</th>
                    <th style="text-align:center;">24-39</th>
                    <th style="text-align:center;">40-59</th>
                    <th style="text-align:center;">60-74</th>
                    <th style="text-align:center;">75-89</th>
                    <th style="text-align:center;">90-96</th>
                    <th style="text-align:center;">97-99</th>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Performance</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 1 && $mayaResult['percentile'] <= 3 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 4 && $mayaResult['percentile'] <= 9 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 10 && $mayaResult['percentile'] <= 23 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 24 && $mayaResult['percentile'] <= 39 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 40 && $mayaResult['percentile'] <= 59 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 60 && $mayaResult['percentile'] <= 74 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 75 && $mayaResult['percentile'] <= 89 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 90 && $mayaResult['percentile'] <= 96 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    <td style="color:green;">{!! isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 97 && $mayaResult['percentile'] <= 99 ? '<i class="bi bi-check2 fs-custom"></i>' : '' !!}</td>
                    {{-- <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 1 && $mayaResult['percentile'] <= 3)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 4 && $mayaResult['percentile'] <= 9)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 10 && $mayaResult['percentile'] <= 23)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 24 && $mayaResult['percentile'] <= 39)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 40 && $mayaResult['percentile'] <= 59)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 60 && $mayaResult['percentile'] <= 74)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 75 && $mayaResult['percentile'] <= 89)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 90 && $mayaResult['percentile'] <= 96)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td>
                    <td style="color:green;">
                        @if (isset($mayaResult['percentile']) && $mayaResult['percentile'] >= 97 && $mayaResult['percentile'] <= 99)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                            </svg>
                        @endif
                    </td> --}}
                </tr>
            </table>
        </div>
    </div>
    <div id="maya-answers" class="d-flex text-nowrap" style="width: fit-content;">
        @foreach ($answerList ?? [] as $s => $set)
            <div class="card">
                <div class="card-header">
                    Set {{ strtoupper($s) }}
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($set as $i => $item)
                        <li class="list-group-item">
                            <small class="text-muted me-3">{{ $i }}.</small>
                            <span class="me-2">{{ ($applicant?->maya?->maya_ans[$s . $i] ?? '') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@stop
