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

<style type="text/css">
	#contract-start-date,
	#contract-end-date {
		width: fit-content;
	}

	#form-contract *,
	#tbl-contract_wrapper *,
	#tbl-contract * {
		font-size: 12px;
	}

	#viewFileModal .modal-body embed {
		margin-top: 1rem;
		border: 1px solid black;
	}

	#viewFileModal .modal-body embed + embed {
		margin-top: 1rem;
	}
</style>

<script type="text/javascript">
	var table, selectedRow;
	$(function(){
		table = $('#tbl-contract').DataTable({
            'scrollY': '75vh',
            'scrollCollapse': true,
            'ordering': false
        });

		$('#div-contracts').on('click', '.btn-form-contract', function(){
			let btn = $(this);
			selectedRow = btn.closest('tr').index();
			$('#contract-id').val(btn.data('id') || '');
			$('#contract-emp').val(btn.data('emp') || '');
			$('#contract-description').val(btn.data('description') || '');
			$('#contract-start-date').val(btn.data('start') || '');
			$('#contract-end-date').val(btn.data('end') || '');
			$('#contract-file').val('');
			$('#curfiles').html('');
			let f = btn.data('file') || [];
			for(i in f){
				$('#curfiles').append('<tr><td style="min-height: 20px; height: fit-content;"><embed src="'+'/file/get/contract/'+f[i]+'" class="curfile" filename="'+f[i]+'" style="max-width: 100%; height: 150px;" /></td><td><button class="btn btn-sm btn-close"></button></td></tr>');
			}

			$('#contract-emp').selectpicker('refresh');

			$('#contractModal').modal('show');
		});

		$('#curfiles').on('click', '.btn-close', function(){
			$(this).closest('tr').remove();
		});

		$("#form-contract").submit(async function(e){
	        e.preventDefault();
			$('#err-msg').html("");

			let empname = $('#contract-emp option:selected').text();

			let formData = new FormData();
			formData.append('id', $('#contract-id').val());
			formData.append('emp', $('#contract-emp').val());
			formData.append('description', $('#contract-description').val());
			formData.append('start-date', $('#contract-start-date').val());
			formData.append('end-date', $('#contract-end-date').val());
			formData.append('curfiles', JSON.stringify($('#curfiles .curfile').map((_, el) => $(el).attr('filename')).get()));

			const files = $('#contract-file')[0].files;  // Get the selected files from the input

		    // Loop through each file and append it to the FormData object
		    for (let i = 0; i < files.length; i++) {
		        formData.append('files[]', files[i]);  // Use 'files[]' to send multiple files
		    }

			let response = await fetch('/contracts/save', {
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
				let rowData = [
					empname,
					result.data['description'],
					result.data['start-date'],
					result.data['end-date'],
					JSON.parse(result.data['filenames']).length ? `<button class="btn btn-info" onclick="viewFiles('${convertHTML(result.data['filenames'])}')"><i class="fa fa-file"></i></button>` : ``,
					`<button class="btn btn-outline-secondary btn-form-contract"
					data-id="${result.data['id']}"
					data-emp="${result.data['emp']}"
					data-start="${result.data['start-date']}"
					data-end="${result.data['end-date']}"
					data-description="${result.data['description']}"
					data-file="${convertHTML(result.data['filenames'])}"><i class="fa fa-edit"></i></button>
					<button class="btn btn-outline-danger" onclick="remove_contract('${result.data['id']}')"><i class="fa fa-trash"></i></button>`
				];
				if($('#contract-id').val() && selectedRow !== undefined){
					table.row(selectedRow).data(rowData).draw();
				}else{
					table.row.add(rowData).draw();
				}
			} else {
				$('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
			}
      	});
	});

	async function remove_contract(e) {
        if (confirm('Are you sure you want to delete this record?')) {
            try {
                const response = await fetch('/contracts/'+$(e).data('id'), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await response.json();

                if (data.success) {
					table.row($(e).closest('tr').remove()).delete();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Unable to remove record.');
            }
        }
    }

	function viewFiles(files) {
		$('#viewFileModal .modal-body').html('');
		$('#viewFileModal').modal('show');
		let f = JSON.parse(files);
		for(i in f){
			$('#viewFileModal .modal-body').append('<embed src="'+'/file/get/contract/'+f[i]+'" width="100%" height="100%" />');
		}

		$('#viewFileModal').modal('show');
	}

	function convertHTML(str) {
		let replacements = {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
		    '"': "&quot;",//THIS PROBLEM ME NO MORE THANKS TO ieahleen
		    "'": "&apos;",
		    "<>": "&lt;&gt;",
		}
		return str.replace(/(&|<|>|"|'|<>)/gi, function(noe) {
			return replacements[noe];
		}
	);
}
</script>

<div class="container-fluid" id="div-contracts">
	<div class="float-end mb-3">
		<button class="btn btn-outline-primary btn-form-contract"><i class="fa fa-plus"></i></button>
	</div>
	<table id="tbl-contract" class="table table-sm table-striped table-hover">
		<thead>
			<tr>
				<th class="text-start">Name</th>
				<th class="text-start">Description</th>
				<th class="text-start">Start</th>
				<th class="text-start">End</th>
				<th class="text-start">Files</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			@foreach($list as $l)
			<tr>
				<td>{{ $l->empname }}</td>
				<td>{{ $l->ci_description }}</td>
				<td class="text-start">{{ $l->ci_startdate }}</td>
				<td class="text-start">{{ $l->ci_enddate }}</td>
				<td>
					@if(!empty(json_decode($l->ci_file, true)))
						<button class="btn btn-info" onclick="viewFiles('{{ $l->ci_file }}')"><i class="fa fa-file"></i></button>
					@endif
				</td>
				<td>
					<button class="btn btn-outline-secondary btn-form-contract"
					data-id="{{ $l->ci_id }}"
					data-emp="{{ $l->ci_empno }}"
					data-start="{{ $l->ci_startdate }}"
					data-end="{{ $l->ci_enddate }}"
					data-description="{{ $l->ci_description }}"
					data-file="{{ $l->ci_file }}"><i class="fa fa-edit"></i></button>

					<button class="btn btn-outline-danger" data-id="{{ $l->ci_id }}" onclick="remove_contract(this)"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>

<div class="modal fade" id="viewFileModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewFileModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="viewFileModalLabel">Files</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="contractModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="form-contract">
				<div class="modal-body">
					<input type="hidden" id="contract-id" value="">
					<div class="row mb-3">
						<labe class="col-form-label col-md-3">Name</labe>
						<div class="col-md-9">
							<select class="form-control selectpicker" id="contract-emp" title="Select" data-live-search="true" required>
								@foreach ($employees as $k => $v)
									<option job="{{ $v['jrec_position'] }}" value="{{ $v['pers_empno'] }}">{{ $v['pers_lastname'].trim(" ".($v['pers_suffix'] ?? '')).", ".$v['pers_firstname'] }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="row mb-3">
						<labe class="col-form-label col-md-3">Description</labe>
						<div class="col-md-9">
							<input type="text" class="form-control" id="contract-description" required>
						</div>
					</div>
					<div class="row mb-3">
						<labe class="col-form-label col-md-3">Start Date</labe>
						<div class="col-md-9">
							<input type="date" class="form-control" id="contract-start-date" required>
						</div>
					</div>
					<div class="row mb-3">
						<labe class="col-form-label col-md-3">End Date</labe>
						<div class="col-md-9">
							<input type="date" class="form-control" id="contract-end-date" required>
						</div>
					</div>
					<div class="row mb-3">
						<labe class="col-form-label col-md-3">Add File</labe>
						<div class="col-md-9">
							<input type="file" class="form-control" id="contract-file" multiple>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-12">
							<table class="table table-sm" id="curfiles"></table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>
@stop