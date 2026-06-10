<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>


<style type="text/css">
	#irTabContent table,
	#irInfo * {
		font-size: 12px;
	}

	textarea {
		height: fit-content;
	}
</style>
<script type="text/javascript">
	var curtab = 'posted';
	$(function(){
		fetchIRList('posted');

		$('#irTab button').click(function(){
			let stat = $(this).attr('id').replace('-tab', '');
			if((curtab == stat && !$('#' + stat + '-tab-pane').is(':empty')) || (curtab != stat && $('#' + stat + '-tab-pane').is(':empty'))){
				fetchIRList(stat);
			}
			curtab = stat;
		});

        const params1 = new URLSearchParams(window.location.search);
        const view_ir = params1.get('ir');
        if(view_ir) {
            viewIR(view_ir);
        }
	});

    async function fetchIRList(stat) {
        $('#' + stat + '-tab-pane').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/ir/list/${stat.replace('-', ' ')}`);
            
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
		fetchNotifications('ir');
    }

    async function viewIR(id = '') {
        $('#irList').hide();
        $('#irInfo').show();
        $('#irInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch(`/grievance/ir/view/${id}`);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#irInfo').html(html);
        } catch (error) {
            $('#irInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
		fetchNotifications('ir');
    }

    function closeIR() {
    	$('#irInfo').hide();
        $('#irList').show();
        $('#irInfo').html("");
		fetchNotifications('ir');
    }

    async function view13A(id = '', ir = '') {
        $('#irList').hide();
        $('#irInfo').show();
        $('#irInfo').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/grievance/13a/view/'+(ir ? '?ir='+ir : id));
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();
            // Inject the received HTML into the DOM
            $('#irInfo').html(html);
        } catch (error) {
            $('#irInfo').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
            console.error('Error fetching the list:', error);
        }
		fetchNotifications('13a');
    }

    function close13A() {
    	$('#irInfo').hide();
        $('#irList').show();
        $('#irInfo').html("");
		fetchNotifications('13a');
    }

</script>

<div class="container-fluid">
	<div id="irInfo" class="mb-3" style="display: none;"></div>
	<div id="irList">
        <div class="float-end">
        	<button class="btn btn-primary btn-sm" onclick="viewIR()">New</button>
        </div>
		<ul class="nav nav-underline" id="irTab" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-tab-pane" type="button" role="tab" aria-controls="draft-tab-pane" aria-selected="false">Draft</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="posted-tab" data-bs-toggle="tab" data-bs-target="#posted-tab-pane" type="button" role="tab" aria-controls="posted-tab-pane" aria-selected="true">Posted</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="needs-explanation-tab" data-bs-toggle="tab" data-bs-target="#needs-explanation-tab-pane" type="button" role="tab" aria-controls="needs-explanation-tab-pane" aria-selected="false">Needs Explanation</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-tab-pane" type="button" role="tab" aria-controls="resolved-tab-pane" aria-selected="false">Resolved</button>
			</li>
		</ul>
		<div class="tab-content" id="irTabContent">
			<div class="pt-3 tab-pane fade" id="draft-tab-pane" role="tabpanel" aria-labelledby="draft-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade show active" id="posted-tab-pane" role="tabpanel" aria-labelledby="posted-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="needs-explanation-tab-pane" role="tabpanel" aria-labelledby="needs-explanation-tab" tabindex="0"></div>
			<div class="pt-3 tab-pane fade" id="resolved-tab-pane" role="tabpanel" aria-labelledby="resolved-tab" tabindex="0"></div>
		</div>
	</div>
</div>