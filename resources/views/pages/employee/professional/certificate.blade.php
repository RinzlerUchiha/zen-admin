<style>
    #form-educcertificate input,
    #form-educcertificate select,
    #educcertificate-list {
        font-size: 12px;
    }

    #educcertificate-list {
        min-width: 50vw;
        width: fit-content;
    }
</style>

<script type="text/javascript">
    $(function(){
        $('#btn-cancel-edit-educcertificate').click(function(){
            $('#form-educcertificate input, #form-educcertificate select').val('');
            $('#form-educcertificate').toggleClass('d-none');
            $('#educcertificate-list').toggleClass('d-none');
        });
    });

    function edit_educcertificate(e) {
        $('#educcertificate-id').val($(e).data('certid'));
        $('#educcertificate-title').val($(e).data('title'));
        $('#educcertificate-completion-date').val($(e).data('completiondate'));
        $('#educcertificate-location').val($(e).data('location'));
        $('#educcertificate-speaker').val($(e).data('speaker'));
        $('#educcertificate-attachment-current').val($(e).data('attachment'));

        $('#form-educcertificate').toggleClass('d-none');
        $('#educcertificate-list').toggleClass('d-none');
    }

    async function remove_educcertificate(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const response = await fetch('/remove/professional/certificate/{{ $empno }}/'+$(e).data('certid'), {
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
</script>

<div id="educcertificate-list">
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
    <table class="table table-sm table-striped table-hover" id="educcertificate-list-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Completion Date</th>
                <th>Location of Event/Course</th>
                <th>Speaker</th>
                <th>Attachment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($empData as $list)
            <tr>
                <td class="text-nowrap">{{ $list->cert_title }}</td>
                <td class="text-nowrap">{{ $list->cert_date }}</td>
                <td class="text-nowrap">{{ $list->cert_address }}</td>
                <td class="text-nowrap">{{ $list->cert_speaker }}</td>
                <td>
                    @if($list->cert_file)
                        <embed src="{{ '/file/get/certificate/'.$list->cert_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-certid="{{ $list->cert_id }}"
                        data-title="{{ $list->cert_title }}"
                        data-completiondate="{{ $list->cert_date }}"
                        data-location="{{ $list->cert_address }}"
                        data-speaker="{{ $list->cert_speaker }}"
                        data-attachment="{{ $list->cert_file }}"
                        onclick="edit_educcertificate(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-certid="{{ $list->cert_id }}" onclick="remove_educcertificate(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_educcertificate(this)">Add</button>
</div>

<form id="form-educcertificate" enctype="multipart/form-data" name="form-educcertificate" method="post" action="{{ route('save_professional_cert') }}" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="employee-number" id="employee-number" value="{{ $empno }}">
    <input type="hidden" name="educcertificate-id" id="educcertificate-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="educcertificate-title" id="educcertificate-title">
                <label for="educcertificate-title">Title</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="educcertificate-completion-date" id="educcertificate-completion-date">
                <label for="educcertificate-completion-date">Completion Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="educcertificate-location" id="educcertificate-location">
                <label for="educcertificate-location">Location of Event/Course</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="educcertificate-speaker" id="educcertificate-speaker">
                <label for="educcertificate-speaker">Speaker</label>
            </div>
        </div>
        
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="file" class="form-control-plaintext border-bottom" name="educcertificate-attachment" id="educcertificate-attachment">
                <input type="hidden" name="educcertificate-attachment-current" id="educcertificate-attachment-current">
                <label for="educcertificate-attachment">Attachment</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-educcertificate">Cancel</button>
</form>