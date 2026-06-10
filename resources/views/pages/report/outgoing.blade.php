<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

<style type="text/css">
	#outgoing-list * {
		font-size: 12px;
	}
</style>

<script type="text/javascript">
	$(function(){
		get_list();

		const params1 = new URLSearchParams(window.location.search);
        const newIntvw = params1.get('new');
        if(newIntvw) {
            newInterview(newIntvw);
        }
	});

	async function get_list() {
        $('#outgoing-list').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/report/outgoing/list');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#outgoing-list').html(html);
            let table = $('#outgoing-list table').DataTable({
            	scrollY: '50vh',
    			scrollCollapse: true,
            	lengthMenu: [10, 25, 50, { label: 'All', value: -1 }],
            	ordering: false
            });

            table.columns().flatten().each( function ( colIdx ) {
			    // Create the select list and search operation
			    let select = $('<select />')
			        .appendTo(
			            table.column(colIdx).footer()
			        )
			        .on( 'change', function () {
			            table
			                .column( colIdx )
			                .search( $(this).val() )
			                .draw();
			        } );

			    select.append( $('<option value="">ALL</option>') );
			    select.addClass('w-100');
			 
			    // Get the search data for the first column and add to the select list
			    table
			        .column( colIdx )
			        .cache( 'search' )
			        .sort()
			        .unique()
			        .each( function ( d ) {
			            select.append( $('<option value="'+d+'">'+d+'</option>') );
			        } );
			} );

			table.columns().draw();
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

	async function viewInfo(empno) {
		$("#outgoing-list").hide();
		$("#outgoing-info").show();
        $('#outgoing-info').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/exit-interview/list/'+empno);
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#outgoing-info').html(html);
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

	function closeInfo() {
		$("#outgoing-info").hide();
		$("#outgoing-list").show();
	}


	async function viewInterview(id) {
		$('#outgoing-info').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
		try {
			// Make the fetch request to the Laravel controller
			const response = await fetch(`/exit-interview/info/${id}`);
			
			if (!response.ok) { // Check if the response was successful
				throw new Error('Network response was not ok');
			}

			// Get the response text (HTML)
			const html = await response.text();
			// Inject the received HTML into the DOM
			$('#outgoing-info').html(html);
		} catch (error) {
			$('#outgoing-info').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
			console.error('Error fetching the list:', error);
		}
	}

	async function newInterview(empno) {
		$('#outgoing-info').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
		try {
			// Make the fetch request to the Laravel controller
			const response = await fetch(`/exit-interview/new/${empno}`);
			
			if (!response.ok) { // Check if the response was successful
				throw new Error('Network response was not ok');
			}

			// Get the response text (HTML)
			const html = await response.text();
			// Inject the received HTML into the DOM
			$('#outgoing-info').html(html);
		} catch (error) {
			$('#outgoing-info').html("<h3 class='mx-auto my-auto'>Error fetching the list: " + error + "</h3>");
			console.error('Error fetching the list:', error);
		}
	}
</script>

<div class="container-fluid" id="outgoing-list"></div>
<div class="container-fluid" id="outgoing-info" style="display: none;"></div>