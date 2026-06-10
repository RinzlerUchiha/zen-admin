<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
	#_13aTabContent table,
	#grievanceInfo * {
		font-size: 12px;
	}

	textarea {
		height: fit-content;
	}
</style>
<script type="text/javascript">
	var curtab = 'pending';
	$(function(){
		fetch13AList('pending');

		$('#_13aTab button').click(function(){
			let stat = $(this).attr('id').replace('-tab', '');
			if((curtab == stat && !$('#' + stat + '-tab-pane').is(':empty')) || (curtab != stat && $('#' + stat + '-tab-pane').is(':empty'))){
				fetch13AList(stat);
			}
			curtab = stat;
		});

        const params1 = new URLSearchParams(window.location.search);
        const view_13a = params1.get('13a');
        if(view_13a) {
            view13A(view_13a);
        }
	});

    async function fetch13AList(stat) {
        $('#' + stat + '-tab-pane').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/13a/list/${stat.replace('-', ' ')}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#' + stat + '-tab-pane').html(html);
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
		fetchNotifications('13a');
    }

    async function view13A(id = '') {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/13a/view/${id}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
		fetchNotifications('13a');
    }

    function close13A() {
    	$('#grievanceInfo').hide();
        $('#grievanceList').show();
        $('#grievanceInfo').html("");
		fetchNotifications('13a');
    }


	async function viewTranscript(id13a) {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/transcript/view/${id13a}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
    }

    async function view13B(id = '', id13a = '') {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/grievance/13b/view/'+(id13a ? '?13a='+id13a : id));
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
    }

    function close13B() {
    	$('#grievanceInfo').hide();
        $('#grievanceList').show();
        $('#grievanceInfo').html("");
    }


    async function viewCommitment(id13a) {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/commitment/view/${id13a}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
    }

    async function viewLetterOfReply(id13a) {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/reply/view/${id13a}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
    }


    async function viewIR(id = '') {
        $('#grievanceList').hide();
        $('#grievanceInfo').show();
        $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/ir/view/${id}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#grievanceInfo').html(html);
        } catch (error) {
            $('#grievanceInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
        fetchNotifications('ir');
    }

    function closeIR() {
        $('#grievanceInfo').hide();
        $('#grievanceList').show();
        $('#grievanceInfo').html("");
        fetchNotifications('ir');
    }

</script>

<div class="container-fluid">
	{{-- <p>Guide: Creator (Post) > HR (Check) > Issued By (Sign) > Noted By (Sign) > Issue to 13A > Receive/Refuse</p> --}}
	<div id="grievanceInfo" class="mb-3" style="display: none;"></div>
	<div id="grievanceList">
		<div class="float-end">
        	<button class="btn btn-primary btn-sm" onclick="view13A()">New</button>
        </div>
		<ul class="nav nav-underline" id="_13aTab" role="tablist">
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
				<button class="nav-link" id="reviewed-tab" data-bs-toggle="tab" data-bs-target="#reviewed-tab-pane" type="button" role="tab" aria-controls="reviewed-tab-pane" aria-selected="false">Reviewed</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="issued-tab" data-bs-toggle="tab" data-bs-target="#issued-tab-pane" type="button" role="tab" aria-controls="issued-tab-pane" aria-selected="false">Issued</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="received-tab" data-bs-toggle="tab" data-bs-target="#received-tab-pane" type="button" role="tab" aria-controls="received-tab-pane" aria-selected="false">Received</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="refused-tab" data-bs-toggle="tab" data-bs-target="#refused-tab-pane" type="button" role="tab" aria-controls="refused-tab-pane" aria-selected="false">Refused</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="needs-explanation-tab" data-bs-toggle="tab" data-bs-target="#needs-explanation-tab-pane" type="button" role="tab" aria-controls="needs-explanation-tab-pane" aria-selected="false">Needs Explanation</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled-tab-pane" type="button" role="tab" aria-controls="cancelled-tab-pane" aria-selected="false">Cancelled</button>
			</li>
		</ul>
		<div class="tab-content" id="_13aTabContent">
			<div class="pt-3 tab-pane fade" id="draft-tab-pane" role="tabpanel" aria-labelledby="draft-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="checked-tab-pane" role="tabpanel" aria-labelledby="checked-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="reviewed-tab-pane" role="tabpanel" aria-labelledby="reviewed-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="issued-tab-pane" role="tabpanel" aria-labelledby="issued-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="received-tab-pane" role="tabpanel" aria-labelledby="received-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="refused-tab-pane" role="tabpanel" aria-labelledby="refused-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="needs-explanation-tab-pane" role="tabpanel" aria-labelledby="needs-explanation-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="cancelled-tab-pane" role="tabpanel" aria-labelledby="cancelled-tab" tabindex="0"></div>
		</div>
	</div>
</div>