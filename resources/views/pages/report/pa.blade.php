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
	#input-group-pa-year {
		max-width: 150px;
	}

	#pa-list * {
		font-size: 12px;
	}
</style>
<script type="text/javascript">
	$(function(){
		// get_list();
	});

	async function get_list() {
        $('#pa-list').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/report/pa/list/'+$('#pa-year').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#pa-list').html(html);
            let table = $('#pa-list table').DataTable({
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
			    // select.addClass('form-control selectpicker');
			    // select.attr('title', 'Select');
			    // select.attr('data-live-search', true);
			 
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
			// $('.selectpicker').selectpicker();
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>
<div class="container-fluid">
	<div class="d-flex justify-content-end mb-3">
		<div class="input-group input-group-sm" id="input-group-pa-year">
			<span class="input-group-text">Year</span>
			<input type="number" class="form-control" id="pa-year" value="{{ date('Y') }}">
			<button class="btn btn-outline-secondary" onclick="get_list()"><i class="bi bi-search"></i></button>
		</div>
	</div>
	<div id="pa-list"></div>
</div>