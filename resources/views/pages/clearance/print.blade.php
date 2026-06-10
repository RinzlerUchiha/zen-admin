@if (empty($get) && $type == 1)

    <style>
        @page {
            size: auto;
            margin: 0;
            font-size: 12px;
        }

        @media print {
            ._div {
                page-break-after: auto;
            }
        }

        html {
            background-color: #FFFFFF;
            margin: 0px;
        }

        body {
            margin: 1in;
        }

        td {
            font-family: "Arial", Arial, Sans-serif;
            text-align: left;
            vertical-align: top;
        }

        b,
        small {
            font-family: "Arial", Arial, Sans-serif;
        }

        input[type="checkbox"] {
            color: darkred;
            border-color: darkred;
            vertical-align: bottom;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }

        .d-fit {
            width: fit-content;
        }

        .mx-auto {
            margin-right: auto;
            margin-left: auto;
        }

        .div-signature-list {
            display: flex;
            flex-wrap: wrap;
        }

        .div-signature {
            width: 150px;
            position: relative;
            height: fit-content;
            margin-bottom: -10px;
            margin-right: auto;
            margin-left: auto;
        }

        .div-signature svg {
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            display: block;
            width: 100%;
            height: 100%;
            overflow: unset;
        }
    </style>

    @if ($clearance->ecf_company == 'QST')
        <img src="https://teamtngc.com/hris2/img/qstlogo.png" style="height: 0.71in;">
    @elseif($clearance->ecf_company != 'SJI')
        <table width="100%" class="table" style="margin-left: 20px;">
            <tr>
                <td style="padding: 0px 0px 0px 10px; width: 1.28in; height: 0.44in;">
                    <img src="https://teamtngc.com/hris2/img/sti1.png" style="width: 1.28in; height: 0.44in;">
                </td>
                <td style="font-size: 8pt; padding-left: 10px;">
                    <small>College Zamboanga</small><br/>
                    <small>Lim Bros Bldg. (Unicon)</small><br/>
                    <small>Zamboanga City</small>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p>
        {{-- <p>&nbsp;</p> --}}
        <center><b style="font-size: 14pt;">CLEARANCE OF EMPLOYMENT</b></center>
    @else
        <center><img src="https://teamtngc.com/hris2/img/sophia1.png" style="width: 1.93in; height: 0.71in;"></center>
        <p>&nbsp;</p>
        {{-- <p>&nbsp;</p> --}}
        <center><b style="font-size: 14pt;">CLEARANCE</b></center>
    @endif

    <p>&nbsp;</p>
    {{-- <p>&nbsp;</p> --}}

    <table width="100%" class="table" >
        <tbody>
            <tr>
                <td style="padding: 5px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                    <label style="font-size: 11pt;">Employee Name: {{ ucwords($clearance->ecf_name) }}</label>
                </td>
                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial' font-size: 11pt;">
                    <label style="font-size: 11pt;">Purpose of Clearance: Quit Claim & COE</label>
                </td>
            </tr>
            <tr>
                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                    <label style="font-size: 11pt;">Designation: {{ $clearance->position_name }}</label>
                </td>
                <td style="padding: 0px 0px 0px 5px;font-family: 'Arial'; font-size: 11pt;">
                    <label style="font-size: 11pt;">Effectivity Date: {{ date('F d, Y',strtotime($clearance->ecf_lastday)) }}</label>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="div-signature-list">
        @foreach ($catList as $item)
            @if (($loop->index == 0 || ($loop->last && $loop->even)) && $loop->count > 2)
                <div style="width: 100%; display: block; text-align: center;">
                    <table class="mx-auto">
                        <tr>
                            <td>
                                <div class="d-fit">
                                    <div class="div-signature">
                                        {!! $item->catstat_sign !!}
                                    </div>
                                    <div style="text-align: center; font-weight: bold; font-size: 12pt;">
                                        {{ strtoupper($item->clearedby) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: bold; font-size: 12pt;">
                                {{ $item->cat_title }}
                            </td>
                        </tr>
                    </table>
                </div>
            @else
                <div style="width: 50%; display: block; flex-grow: 1;">
                    <table>
                        <tr>
                            <td>
                                <div class="d-fit">
                                    <div class="div-signature">
                                        {!! $item->catstat_sign !!}
                                    </div>
                                    <div style="text-align: left; font-weight: bold; font-size: 12pt;">
                                        {{ strtoupper($item->clearedby) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: ; font-weight: bold; font-size: 12pt;">
                                {{ $item->cat_title }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        @endforeach
    </div>

    <p>&nbsp;</p>
    {{-- <p>&nbsp;</p> --}}

    <p style="font-size: 10pt; font-family: 'Arial';">This is to certify that I am cleared of all accountabilities with the establishment, and I do not have in my possession any of the establishment equipment, materials, records or copies thereof.</p>

    <div style="width: 67%; display: inline-grid; margin-right: 10px;">
        <span style="font-size: 11pt; font-family: 'Arial';">&emsp;&emsp;&emsp;City of Zamboanga, __________________20__</span><br><br><br>
        <div align="right">
            <table>
                <tr>
                    <td style="border-top: 1px black solid; font-size: 11pt; font-family: 'Arial'; text-align: center; width: 170px;">
                        Signature of employee<br>
                        <span style="font-size: 8pt; font-family: 'Arial';">Date: _____________</span>
                    </td>
                </tr>
            </table>
        </div>
        <p>
            To be accomplished in triplicate<br>
            Original &ndash; Accounting<br>
            Duplicate &ndash; 201<br>
            Triplicate &ndash; Employee
        </p>
    </div>

    <div style="width: 30%; display: inline-grid;">
        <table style="border: 1px black solid;">
            <tr>
                <td colspan="2" style="font-size: 9pt; font-family: 'Arial'; font-weight: bold;"><u>Requirements for Clearance</u></td>
            </tr>
            <tr>
                <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                <td style="font-size: 9pt; font-family: 'Arial';">
                    CTC # _________<br>
                    Date Issued: ______<br>
                    Place Issued: ______
                </td>
            </tr>
            <tr>
                <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                <td style="font-size: 9pt; font-family: 'Arial';">Employee ID </td>
            </tr>
            @if($clearance->ecf_company != "SJI")
                <tr>
                    <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                    <td style="font-size: 9pt; font-family: 'Arial';">Training Contract ________</td>
                </tr>
                <tr>
                    <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                    <td style="font-size: 9pt; font-family: 'Arial';">
                        Others: _________<br>
                        &emsp;&emsp;&emsp;&nbsp;__________
                    </td>
                </tr>
            @else
                <tr>
                    <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                    <td style="font-size: 9pt; font-family: 'Arial';">CK credit card</td>
                </tr>
                <tr>
                    <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                    <td style="font-size: 9pt; font-family: 'Arial';">Laptop</td>
                </tr>
                <tr>
                    <td style="width: 10pt;"><div style="width: 9pt; height: 9pt; border: 1px black solid;"></div></td>
                    <td style="font-size: 9pt; font-family: 'Arial';">Others:<br>_________</td>
                </tr>
            @endif
        </table>
    </div>

    <script>
        window.print();
    </script>

@elseif(empty($get) && $type == 2)

    <style>
        @page {
            size: auto;
            margin: 0;
            font-size: 12px;
        }

        @media print {
            ._div {
                page-break-after: auto;
            }
        }

        html {
            background-color: #FFFFFF;
            margin: 0px;
        }

        body {
            margin: 1in;
        }

        td {
            font-family: "Arial", Arial, Sans-serif;
            text-align: left;
            vertical-align: top;
        }

        p, b, small {
            font-family: "Arial", Arial, Sans-serif;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }
    </style>
    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
        @if ($clearance->ecf_company == 'QST')
            <img src="https://teamtngc.com/hris2/img/qstlogo.png" style="height: 0.71in;">
        @elseif($clearance->ecf_company != 'SJI')
            <img src="https://teamtngc.com/hris2/img/sti1.png" style="/* width: 1.28in; */ height: 0.44in;">
        @else
            <img src="https://teamtngc.com/hris2/img/sophia1.png" style="/* width: 1.93in; */ height: 0.71in;">
        @endif
    </div>
    <center><b style="font-size: 25pt; font-family: 'Times New Roman', Times, Sans-serif;">C E R T I F I C A T I O N</b></center>

    <p>&nbsp;</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;&nbsp;This is to certify that <b>{{ $clearance->ecf_name }}</b>  was employed with this Institution as {{ $clearance->position_name }} from {{ date('F d, Y',strtotime($clearance->ecf_hireddt)) }} to {{ date('F d, Y',strtotime($clearance->ecf_lastday)) }}.</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;This certification is issued upon request for whatever legal purpose(s) it may serve {{ $clearance->sex == 'Male' ? 'him' : ($clearance->sex == 'Female' ? 'her' : 'him/her') }} best.</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">&nbsp;&nbsp;&nbsp;Issued this {!! date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y") !!} in the City of Zamboanga, Philippines.</p>
    
    <p>&nbsp;</p>

    <center>
        <table>
            <tr><td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td></tr>
            <tr><td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td></tr>
        </table>
    </center>

@elseif(empty($get) && $type == 3)

    <style>
        @page {
            size: auto;
            margin: 0;
            font-size: 12px;
        }

        @media print {
            ._div {
                page-break-after: auto;
            }
        }

        html {
            background-color: #FFFFFF;
            margin: 0px;
        }

        body {
            margin: 1in;
        }

        td {
            font-family: "Arial", Arial, Sans-serif;
            text-align: left;
            vertical-align: top;
        }

        p, b, small {
            font-family: "Arial", Arial, Sans-serif;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }

        .div-signature {
            width: 150px;
            position: relative;
            height: fit-content;
            margin-bottom: -10px;
            margin-right: auto;
            margin-left: auto;
        }

        .div-signature svg {
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            display: block;
            width: 100%;
            height: 100%;
            overflow: unset;
        }
    </style>
    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
        @if ($clearance->ecf_company == 'QST')
            <img src="https://teamtngc.com/hris2/img/qstlogo.png" style="width: 1in;">
        @elseif($clearance->ecf_company != 'SJI')
            <img src="https://teamtngc.com/hris2/img/sti1.png" style="/* width: 1.28in; */ height: 0.44in;">
        @else
            <img src="https://teamtngc.com/hris2/img/sophia1.png" style="width: 1in;">
        @endif
    </div>

    <table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
        <tr>
            <td style="border: 1px solid black; padding: 3px;">Name: {{ $clearance->ecf_name }}</td>
            <td colspan="2" style="border: 1px solid black; padding: 3px;">Position: {{ $clearance->position_name }}</td>
            <td style="border: 1px solid black; padding: 3px;">Department: {{ $clearance->dept_name }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid black; padding: 3px;">Last day of employment: {{ $clearance->ecf_lastday }}</td>
            <td colspan="2" style="border: 1px solid black; padding: 3px;">Contact Number: {{ $clearance->contact }}</td>
        </tr>
    </table>

    <p>&nbsp;</p>

    <table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">DEPARTMENT/DIRECTOR</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">ITEM/ACCOUNTABILITY</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center;">VERIFIED BY</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($catList as $item)
                <tr>
                    <td style='border: 1px solid black; padding: 3px;'>{{ $item->cat_title }}<br>{{ $item->clearedby }}</td>
                    <td style='border: 1px solid black; padding: 3px;'>
                        @foreach ($item->requirements as $r)
                            <div style='display: block; margin-bottom: 3px;'>
                                <span style='width: 18px; height: 18px; border: 1px solid black; text-align: center; display: inline-block; vertical-align: middle;'>{!! ($r->catreq_required == 0 ? ($r->catreq_clearedby != '' ? "&#x2713;" : "&#x2717;") : "") !!}</span>
                                <span style='vertical-align: middle;'>&nbsp;{{ $r->req_name.($r->catreq_remarks != '' ? " (" . $r->catreq_remarks . ")" : "") }}</span>
                            </div>
                        @endforeach
                    </td>
                    <td style='border: 1px solid black; padding: 3px; height: 50px;'>
                        <div class="div-signature">{!! $item->catstat_sign !!}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@elseif(empty($get) && $type == 4)

    <style>
        @page {
            size: auto;
            margin: 0;
            font-size: 12px;
        }

        @media print {
            ._div {
                page-break-after: auto;
            }
        }

        html {
            background-color: #FFFFFF;
            margin: 0px;
        }

        body {
            margin: 1in;
        }

        td {
            font-family: "Arial", Arial, Sans-serif;
            text-align: left;
            vertical-align: top;
        }

        p, b, small {
            font-family: "Arial", Arial, Sans-serif;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }
    </style>

    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
        @if ($clearance->ecf_company == 'QST')
            <img src="https://teamtngc.com/hris2/img/qstlogo.png" style="width: 1.28in;">
        @elseif($clearance->ecf_company != 'SJI')
            <img src="https://teamtngc.com/hris2/img/sti1.png" style="width: 1.28in;">
        @else
            <img src="https://teamtngc.com/hris2/img/sophia1.png" style="width: 1.28in;">
        @endif
    </div>

    <center><b style="font-size: 20pt; font-family: 'Arial', Arial, Sans-serif;">Certificate of Employment</b></center>
    
    <p>&nbsp;</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This is to certify that <b>{{ strtoupper($clearance->ecf_name) }}</b>  has been employed with our Institution from {{ date('F d, Y',strtotime($clearance->ecf_hireddt)) }} to {{ date('F d, Y',strtotime($clearance->ecf_lastday)) }} as {{ $clearance->position_name }}.</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">{{ $clearance->pronoun[2] }} {{ $clearance->empinfo?->pers_lastname . (substr($clearance->empinfo?->pers_lastname, -1) == 's' ? '\'' : '\'s') }} performance was highly commendable. {{ ucwords($clearance->pronoun[0]) }} was of good standing while working in our institution. I can attest that {{ $clearance->pronoun[0] }} fully respected the school's policies, conducted {{ $clearance->pronoun[1] }}self in a professional way, and adhered to the ethics of professional teachers. Above all, {{ $clearance->pronoun[0] }} satisfactorily delivered the institution's promise of assisting students to become College ready, Job ready and Life ready by being the best teacher that {{ $clearance->pronoun[0] }} can be.</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This certification is issued upon request of {{ ucwords($clearance->pronoun[2]) }} {{ ucwords($clearance->empinfo?->pers_lastname) }} for whatever legal purpose it may serve {{ $clearance->pronoun[1] }} best.</p>
    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">Given this {!! date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y") !!} at STI College Zamboanga, Gov. Lim Avenue, Zamboanga City, Philippines.</p>

    <p>&nbsp;</p>

    <div align="center">
        <table>
            <tr><td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td></tr>
            <tr><td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td></tr>
        </table>
    </div>

@elseif(empty($get) && $type == 5)

    <style>
        @page {
            size: auto;
            margin: 0;
            font-size: 12px;
        }

        @media print {
            ._div {
                page-break-after: auto;
            }
        }

        html {
            background-color: #FFFFFF;
            margin: 0px;
        }

        body {
            margin: 1in;
        }

        td {
            font-family: "Arial", Arial, Sans-serif;
            text-align: left;
            vertical-align: top;
        }

        p, b, small {
            font-family: "Arial", Arial, Sans-serif;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }
    </style>

    <div style="position: absolute; left: 0; top: 0; margin-top: 20px;margin-left: 20px;">
        @if ($clearance->ecf_company == 'QST')
            <img src="https://teamtngc.com/hris2/img/qstlogo.png" style="width: 1.28in;">
        @elseif($clearance->ecf_company != 'SJI')
            <img src="https://teamtngc.com/hris2/img/sti1.png" style="width: 1.28in;">
        @else
            <img src="https://teamtngc.com/hris2/img/sophia1.png" style="width: 1.28in;">
        @endif
    </div>

    <center><b style="font-size: 20pt; font-family: 'Arial', Arial, Sans-serif;">Certificate of Employment</b></center>
    
    <p>&nbsp;</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This is to certify that <b>{{ strtoupper($clearance->pronoun[2]." ".$clearance->ecf_name) }}</b>  has been employed with our Institution from {{ date('F d, Y',strtotime($clearance->ecf_hireddt)) }} to {{ date('F d, Y',strtotime($clearance->ecf_lastday)) }} as {{ $clearance->position_name }}.</p>

    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">{{ ucwords($clearance->pronoun[3]) }} performance was average. {{ ucwords($clearance->pronoun[0]) }} complied with the rules and regulations of the company during {{ $clearance->pronoun[3] }} stay.</p>
    
    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">This certification is issued upon request of {{ ucwords($clearance->pronoun[2]) }} {{ ucwords($clearance->empinfo?->pers_lastname) }} for whatever legal purpose it may serve {{ $clearance->pronoun[1] }} best.</p>
    
    <p style="font-size: 15pt; word-spacing: 3px; text-align: justify; text-justify: inter-word;">Given this {!! date("j\<\s\u\p\>S\<\/\s\u\p\> \d\a\y \of F Y") !!} at STI College Zamboanga, Gov. Lim Avenue, Zamboanga City, Philippines.</p>
    
    <p>&nbsp;</p>

    <div align="center">
        <table>
            <tr><td style="font-size: 20px; word-spacing: 3px; text-align: center;"><b>Atty. Angelique Margret T. Natividad</b></td></tr>
            <tr><td style="padding: 3px 0px 0px 0px;font-size: 20px; border-top: 1px solid black; word-spacing: 3px; text-align: center;">HR Director</td></tr>
        </table>
    </div>

@endif