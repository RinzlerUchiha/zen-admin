<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

<style>
    #clrTabContent,
    #modal-clr,
    #modal-clr input,
    #modal-clr select,
    #catList *,
    #modal-view-clr,
    #view-catList * {
        font-size: 12px;
    }

    #view-catList {
        max-height: 65vh;
        overflow-y: auto;
    }

    .bootstrap-select{
        max-width: 100% !important;
    }

    @media (min-width: 992px) {
        .border-lg-custom {
            border-right: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color) !important;
        }
    }

    .div-signature {
    	width: 150px;
    	position: relative;
    	height: fit-content;
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

    tr.not-required td {
        text-decoration: line-through;
        --bs-text-opacity: .5;
        color: rgba(var(--bs-secondary-rgb), var(--bs-text-opacity)) !important;
    }
</style>
<script>
    let curtab = 'pending';
    let saveAsDraft = 0, isPosted = 1;
    let reqFormData, curSrc;

    var canvas, signaturePad, curSignature;

	function resizeCanvas() {
		if (canvas) {
			let ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear();
		}
	}
    
    $(function(){
        canvas = document.getElementById("signature-pad-canvas");
		if(canvas){
			signaturePad = new SignaturePad(canvas, {
				backgroundColor: 'rgb(255, 255, 255)'
			});

			signaturePad.minWidth = 3;
			signaturePad.maxWidth = 3;
		}

		window.onresize = resizeCanvas;

		$('#signModal button[data-action="clear"]').click(function(){
			signaturePad.clear();
		});

        $('#clrTab .nav-item .nav-link').click(function(){
            let stat = $(this).attr('id').replace('-tab', '');
            if((curtab == stat && !$('#' + stat + '-tab-pane').is(':empty')) || (curtab != stat && $('#' + stat + '-tab-pane').is(':empty'))){
                getList(stat);
            }
            curtab = stat;
        });

        $('#modal-clr').on('show.bs.modal', function(e){
            let src = $(e.relatedTarget);
            saveAsDraft = 0;
            
            $('#form-clr button[type="submit"]').hide();
            
            $('#clr-id').val(src.data('id'));
            $('#clr-company').val(src.data('company'));
            $('#clr-empno').val(src.data('empno'));
            $('#clr-last-day').val(src.data('lastday'));
            $('#clr-separation-type').val(src.data('separation') || 1);
            $('#clr-resign-date').val(src.data('resigndt'));

            $('#clr-empno option').hide();
            $('#clr-empno').selectpicker('refresh');

            if(!src.data('stat') || src.data('stat') == 'draft'){
                $('#btn-draft-clr').show();
            }

            if(!src.data('stat') || src.data('stat') == 'draft' || src.data('stat') == 'pending'){
                $('#btn-post-clr').show();
            }

            if(src.data('stat') && src.data('stat') != 'draft' && src.data('stat') != 'pending'){
                $('#btn-save-clr').show();
            }

            getCat();
        });

        $('#modal-view-clr').on('show.bs.modal', function(e){
            let src = $(e.relatedTarget);

            $('#view-clr-id').val(src.data('id'));
            $('#view-clr-company').text(src.data('company'));
            $('#view-clr-employee').text(src.data('empname'));
            $('#view-clr-last-day').text(src.data('lastday'));
            $('#view-clr-separation-type').text(src.data('separationname'));
            $('#view-clr-salary-hold-date').text(src.data('salaryholddate'));
            $('#view-clr-resign-date').text(src.data('resigndt'));

            $('#btn-edit-clr').attr({
                'data-id': src.data('id'),
                'data-company': src.data('company'),
                'data-empno': src.data('empno'),
                'data-empname': src.data('empname'),
                'data-lastday': src.data('lastday'),
                'data-separation': src.data('separation'),
                'data-salaryholddate': src.data('salaryholddate'),
                'data-resigndt': src.data('resigndt'),
                'data-stat': src.data('stat')
            });

            getAttachmentList();

            getCatDetails();
        });

        $('#clr-company').change(function(){
            // $('#clr-company-container').hide();
            // $('#clr-details').show();
            $('#clr-empno option').hide();
            $('#clr-empno option[data-company="' + this.value + '"]').show();            
            // $('#clr-empno').selectpicker('refresh');
            $('#clr-empno').val('');
            getCat();
        });

        $('#btn-draft-clr').click(function(){
            saveAsDraft = 1;
            isPosted = 0;
        });

        $('#btn-post-clr').click(function(){
            saveAsDraft = 0;
            isPosted = 1;
        });
        
        $('#form-clr').submit(async function(e) {
            e.preventDefault();

            $('#clr-err').html("");

            let formData = new FormData();
            formData.append('id', $('#clr-id').val());
            formData.append('company', $('#clr-company').val());
            formData.append('empno', $('#clr-empno').val());
            formData.append('separationType', $('#clr-separation-type').val());
            formData.append('lastDay', $('#clr-last-day').val());
            formData.append('resignDate', $('#clr-resign-date').val());
            formData.append('saveAsDraft', saveAsDraft);
            formData.append('isPosted', isPosted);
            
            let clrList = [];
            $('#catList .clr-cat').each(function(){
                if($(this).parent().find('select.clr-cat-checker').val()){
                    clrList.push([
                        $(this).val(),
                        $(this).parent().find('select.clr-cat-checker').val()
                    ]);
                }
            });
            
            if(clrList.length == 0){
                alert('No checker selected');
                return;
            }

            formData.append('checkerList', JSON.stringify(clrList));

            let response = await fetch('/clearance/save', {
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
                
                if(isPosted){
                    if($('#clrTab button.active').attr('id').replace('-tab', '') !== 'pending'){
                        getList($('#clrTab button.active').attr('id').replace('-tab', ''));
                        $('#pending-tab').click();
                    }
                    getList('pending');
                }else if(saveAsDraft){
                    if($('#clrTab button.active').attr('id').replace('-tab', '') !== 'draft'){
                        getList($('#clrTab button.active').attr('id').replace('-tab', ''));
                        $('#draft-tab').click();
                    }
                    getList('draft');
                }
            } else {
                $('#clr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });


        $('#btn-sign-proceed').click(function(){
            reqFormData.append('signature', signaturePad.toSVG());
            $('#signModal').modal('hide');
            saveRequirementStat();
            resetScreen();
        });

        $('#signModal').on('shown.bs.modal', function(e){
            resizeCanvas();
        });


        $('#form-clr-attachment').submit(async function(e){
            e.preventDefault();
            $('#view-clr-err').html('');
            const file = $('#clr-attachment-file')[0].files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('clr', $('#view-clr-id').val());
            formData.append('desc', $('#clr-attachment-desc').val());
            formData.append('file', file);

            let response = await fetch('/clearance/attachment/save', {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            });

            let result = await response.json();

            if (response.ok && result.success) {
                getAttachmentList();
                $('#form-clr-attachment').hide();
                $('#clr-attachment-list').show();
                $('#btn-clr-file-add').show();
            } else {
                $('#view-clr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        $('#btn-clr-file-add').click(function(){
            $('#form-clr-attachment').show();
            $('#clr-attachment-list').hide();
            $('#btn-clr-file-add').hide();
        });

        $('#btn-clr-file-cancel').click(function(){
            $('#form-clr-attachment').hide();
            $('#clr-attachment-list').show();
            $('#btn-clr-file-add').show();
        });

        getList('pending');

        const url = new URL(window.location.href);
        const params = url.searchParams;
        const viewClr = params.get('clr');
        params.delete('clr');
        history.replaceState(null, '', url);
        if(viewClr) {
            fetch('/clearance/info/' + viewClr)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json(); // or .text(), .blob(), etc.
            })
            .then(data => {
                let btn1 = $('<button/>')
                .addClass('d-none')
                .attr('data-bs-toggle', 'modal')
                .attr('data-bs-target', data.stat == 'draft' ? '#modal-clr' : '#modal-view-clr')
                .data({
                    'id': data.id,
                    'company': data.company,
                    'empno': data.empno,
                    'empname': data.empname,
                    'lastday': data.lastday,
                    'separation': data.separation,
                    'separationname': data.separationname,
                    'salaryholddate': data.salaryholddate,
                    'resigndt': data.resigndt,
                    'stat': data.stat
                })
                .appendTo('body');

                btn1.click();
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
        }
    })

    async function getList(stat) {
        $('#'+stat+'-tab-pane').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/clearance/list/' + stat);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#'+stat+'-tab-pane').html(html);
            $('#'+stat+'-tab-pane > table').DataTable({
                scrollY: '55vh',
                scrollCollapse: true,
                lengthMenu: [50, 100, { label: 'All', value: -1 }],
                ordering: false
            });
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function getCat() {
        try {
            $('#catList').html('Loading list...');
            // Make the fetch request to the Laravel controller
            const response = await fetch('/clearance/cat/' + $('#clr-company').val() + ($('#clr-id').val() ? '/' + $('#clr-id').val() : ''));
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#catList').html(html);
            $('.selectpicker').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function getCatDetails() {
        try {
            $('#view-catList').html('Loading list...');
            // Make the fetch request to the Laravel controller
            const response = await fetch('/clearance/cat-details/' + $('#view-clr-id').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#view-catList').html(html);
            $('.selectpicker').selectpicker('refresh');
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    function checkRequirements(el) {
        $('#view-clr-err').html('');
        reqFormData = new FormData();
        reqFormData.append('id', $('#view-clr-id').val());
        reqFormData.append('cat', $(el).closest('.cat-item').data('cat'));
        reqFormData.append('stat', $(el).data('action'));
        let req = [];
        fill_err = 0;
        $(el).closest('.cat-item').find('.cat-req-item').each(function(){
            req.push({
                'reqid': $(this).data('id'),
                'required': $(this).find('.cat-req-na:checked').length ? 0 : 1, // if checked, not required
                'date': $(this).find('.cat-req-verified-date').val(),
                'verifiedby': $(this).find('select.cat-req-verified-by').val(),
                'remarks': $(this).find('.cat-req-remarks').val()
            });
            
            if($(el).data('action') == 'cleared'
                && $(this).find('.cat-req-na:checked').length == 0 
                && (!$(this).find('.cat-req-verified-date').val() 
                    || !$(this).find('select.cat-req-verified-by').val())){
                $('#view-clr-err').html('<p style="color: red;">Please select NA or fill up Date Verified & Verified By to clear</p>');
                fill_err = 1;
                return false;
            }
        });

        if(fill_err) return;

        reqFormData.append('requirements', JSON.stringify(req));

        if($(el).data('action') == 'cleared'){
            rotateScreen();
        }else{
            saveRequirementStat();
        }
    }

    async function saveRequirementStat() {
        $('#view-clr-err').html("");

        let response = await fetch('/clearance/requirements', {
            method: "POST",
            body: reqFormData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            }
        });

        let result = await response.json();

        if (response.ok && result.success) {
            signaturePad.clear();
            // $('.modal').modal('hide');
            alert('Saved');
            getCatDetails();
        } else {
            $('#clr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
        }
    }

    async function getAttachmentList() {
        $('#clr-attachment-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/clearance/attachment/list/' + $('#view-clr-id').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#clr-attachment-list').html(html);
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function delAttachment(id) {
        if(confirm('Are you sure?')){
            try {
                // Make the fetch request to the Laravel controller
                const response = await fetch('/clearance/attachment/' + id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (!response.ok) { // Check if the response was successful
                    throw new Error('Network response was not ok');
                }

                let result = await response.json();

                if (response.ok && result.success) {
                    getAttachmentList();
                } else {
                    $('#view-clr-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            } catch (error) {
                console.error('Error fetching the list:', error);
            }
        }
    }

    async function rotateScreen() {
        try {
            await document.documentElement.requestFullscreen();
            await screen.orientation.lock('landscape');
        } catch (error) {
            if ($(window).height() > $(window).width()) {
                alert("Please rotate phone to landscape");
                return;
            }
        }
        $('#signModal').modal('show');
    }

    function resetScreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        }
        if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
        }
    }

</script>

<div class="d-flex justify-content-end">
    <button class="ms-auto btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-clr">Create Clearance</button>
</div>
<ul class="nav nav-underline" id="clrTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-tab-pane" type="button" role="tab" aria-controls="draft-tab-pane" aria-selected="false">Draft</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-controls="pending-tab-pane" aria-selected="true">Pending</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="checked-tab" data-bs-toggle="tab" data-bs-target="#checked-tab-pane" type="button" role="tab" aria-controls="checked-tab-pane" aria-selected="false">Checked</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cleared-tab" data-bs-toggle="tab" data-bs-target="#cleared-tab-pane" type="button" role="tab" aria-controls="cleared-tab-pane" aria-selected="false">Cleared</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-tab-pane" type="button" role="tab" aria-controls="cancelled-tab-pane" aria-selected="false">Cancelled</button>
    </li>
</ul>
<div class="tab-content" id="clrTabContent">
    <div class="pt-3 tab-pane fade" id="draft-tab-pane" role="tabpanel" aria-labelledby="draft-tab" tabindex="0"></div>
    <div class="pt-3 tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0"></div>
    <div class="pt-3 tab-pane fade" id="checked-tab-pane" role="tabpanel" aria-labelledby="checked-tab" tabindex="0"></div>
    <div class="pt-3 tab-pane fade" id="cleared-tab-pane" role="tabpanel" aria-labelledby="cleared-tab" tabindex="0"></div>
    <div class="pt-3 tab-pane fade" id="cancelled-tab-pane" role="tabpanel" aria-labelledby="cancelled-tab" tabindex="0"></div>
</div>

<div class="modal fade" id="modal-clr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
aria-labelledby="modal-clr-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-clr-label">Clearance Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-clr">
                <div class="modal-body">
                    <div class="row" id="clr-err"></div>
                    <input type="hidden" id="clr-id" value="">
                    <fieldset>
                        <div class="row g-3" id="clr-details" >
                            <div class="col-lg-5 border-lg-custom">
                                <div class="row mb-3" id="clr-company-container">
                                    <label class="col-form-label col-md-4">Select Company</label>
                                    <div class="col-auto">
                                        <select class="form-select" id="clr-company">
                                            <option value disabled selected>-</option>
                                            @foreach ($companyList as $item)
                                                <option class="text-start" value="{{ $item->C_Code }}">{{ $item->C_Name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-4">Employee</label>
                                    <div class="col-md-auto">
                                        <select class="form-control form-control-sm selectpicker" data-width="auto" id="clr-empno" title="Select Employee" data-live-search="true" required>
                                            @foreach ($employees as $v)
                                                <option data-company="{{ $v['jrec_company'] }}" value="{{ $v['pers_empno'] }}" style="display: none;">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-4">Separation Type</label>
                                    <div class="col-md-auto">
                                        <select class="form-select form-select-sm" id="clr-separation-type" required>
                                            @foreach ($separationList as $v)
                                            <option value="{{ $v->sep_id }}">{{ $v->sep_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-4">Last Day</label>
                                    <div class="col-md-auto">
                                        <input type="date" class="form-control form-control-sm" id="clr-last-day" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-4">Salary hold date:</label>
                                    <label class="col-form-label col-md-auto" id="clr-salary-hold-date">-</label>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-form-label col-md-4">Resign Date</label>
                                    <div class="col-md-auto">
                                        <input type="date" class="form-control form-control-sm" id="clr-resign-date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7" id="catList"></div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-light" id="btn-draft-clr">Save as draft</button>
                    <button type="submit" class="btn btn-primary" id="btn-post-clr">Post</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-clr" style="display: none;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-view-clr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
aria-labelledby="modal-clr-label" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-clr-label">Clearance Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="view-clr-err"></div>
                <input type="hidden" id="view-clr-id" value="">
                <div class="row g-3" id="view-clr-details" >
                    <div class="col-lg-4 border-lg-custom">
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Select Company:</span>
                            <span class="col-form-label col-md" id="view-clr-company">-</span>
                        </div>
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Employee:</span>
                            <span class="col-form-label col-md" id="view-clr-employee">-</span>
                        </div>
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Separation Type:</span>
                            <span class="col-form-label col-md" id="view-clr-separation-type">-</span>
                        </div>
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Last Day:</span>
                            <span class="col-form-label col-md" id="view-clr-last-day">-</span>
                        </div>
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Salary hold date:</span>
                            <span class="col-form-label col-md" id="view-clr-salary-hold-date">-</span>
                        </div>
                        <div class="row mb-2">
                            <span class="col-form-label col-md-4">Resign Date:</span>
                            <span class="col-form-label col-md" id="view-clr-resign-date">-</span>
                        </div>
                        <div class="row">
                            <div class="col">
                                <span class="form-label">Attachments:</span>
                                <div class="mb-2" id="clr-attachment-list">-No Attachments-</div>
                                <button class="btn btn-outline-primary btn-sm" id="btn-clr-file-add">Add File</button>
                                <form id="form-clr-attachment" style="display: none;">
                                    <div class="mb-3">
                                        <label for="clr-attachment-desc" class="form-label form-label-sm">Description</label>
                                        <input type="text" class="form-control form-control-sm mb-2" placeholder="File Description" id="clr-attachment-desc">
                                        <input type="file" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" id="clr-attachment-file">
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-secondary" id="btn-clr-file-cancel">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-primary ms-1">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 px-3" id="view-catList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modal-clr" id="btn-edit-clr">Edit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="signModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="signModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="signModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetScreen()"></button>
            </div>
            <div class="modal-body">
                <div class="d-block h-100 border border-3">
                    <canvas id="signature-pad-canvas" class="signature-pad-canvas h-100 w-100"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-action="clear" onclick="resetScreen()">Cancel</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-action="clear">Reset</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-sign-proceed">Proceed</button>
            </div>
        </div>
    </div>
</div>