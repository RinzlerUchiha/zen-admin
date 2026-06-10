<style>
    #form-contract input,
    #form-contract select,
    #contract-list {
        font-size: 12px;
    }

    #contract-list {
        min-width: 50vw;
        width: fit-content;
    }

    input[type="file"] {
        padding-top: 1.7rem !important;
    }

    /*input[type="file"]::-webkit-file-upload-button {
        vertical-align: middle;
        height: 100%;
    }*/
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-contract').click(function(){
            $('#form-contract input, #form-contract select').val('');
            $('#form-contract').toggleClass('d-none');
            $('#contract-list').toggleClass('d-none');
        });

		$('#curfiles').on('click', '.btn-close', function(){
			$(this).closest('tr').remove();
		});
    });

    function edit_contract(e) {
        $('#contract-id').val($(e).data('id'));
        $('#contract-description').val($(e).data('description') || '');
        $('#contract-start-date').val($(e).data('start') || '');
        $('#contract-end-date').val($(e).data('end') || '');
        // $('#contract-attachment-current').val($(e).data('attachment'));
        $('#curfiles').html('');
        let f = $(e).data('file') || [];
        for(i in f){
            $('#curfiles').append('<tr><td style="min-height: 20px; height: fit-content;"><input type="hidden" name="contract-attachment-current[]" value="'+f[i]+'"><embed src="'+'/file/get/contract/'+f[i]+'" style="max-width: 100%; height: 150px;" /></td><td><button type="button" class="btn btn-sm btn-close"></button></td></tr>');
        }

        $('#form-contract').toggleClass('d-none');
        $('#contract-list').toggleClass('d-none');
    }

    async function remove_contract(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const response = await fetch('/remove/work/contract/'+$(e).data('id'), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    $(e).closest('tr').remove();
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
</script>

<div id="contract-list">
    @if(session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <table class="table table-sm table-striped table-hover" id="contract-list-table">
        <thead>
			<tr>
				<th class="text-start">Description</th>
				<th class="text-start">Start</th>
				<th class="text-start">End</th>
				<th class="text-start">Files</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			@foreach($empData as $l)
			<tr>
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
					data-file="{{ $l->ci_file }}"
                    onclick="edit_contract(this)"><i class="fa fa-edit"></i></button>

					<button class="btn btn-outline-danger" data-id="{{ $l->ci_id }}" onclick="remove_contract(this)"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			@endforeach
		</tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_contract(this)">Add</button>
</div>

<form id="form-contract" enctype="multipart/form-data" name="form-contract" method="post" action="{{ config('app.url') }}/save/work/contract" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="employee-number" id="employee-number" value="{{ $empno }}">
    <input type="hidden" name="contract-id" id="contract-id" value="">
    <div class="row">
        <div class="col-lg">
            <div class="row g-3">
                <div class="col-lg">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="contract-description" id="contract-description">
                        <label for="contract-description">Description</label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control-plaintext border-bottom" name="contract-start-date" id="contract-start-date">
                        <label for="contract-start-date">Start Date</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control-plaintext border-bottom" name="contract-end-date" id="contract-end-date">
                        <label for="contract-end-date">End Date</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Save</button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-contract">Cancel</button>
        </div>
        <div class="col-lg-5">
            <div class="form-floating mb-3">
                <input type="file" class="form-control-plaintext border-bottom" name="contract-attachment[]" id="contract-attachment" multiple>
                <input type="hidden" name="contract-attachment-current" id="contract-attachment-current">
                <label for="contract-attachment">Add Files</label>
            </div>
            <h6>Current Files:</h6>
            <table class="table table-sm" id="curfiles"></table>
        </div>
    </div>
</form>

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