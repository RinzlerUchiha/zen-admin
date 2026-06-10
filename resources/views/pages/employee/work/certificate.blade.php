<style>
    #form-internalcertificate input,
    #form-internalcertificate select,
    #internalcertificate-list {
        font-size: 12px;
    }

    #internalcertificate-list {
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
        $('#btn-cancel-edit-internalcertificate').click(function(){
            $('#form-internalcertificate input, #form-internalcertificate select').val('');
            $('#form-internalcertificate').toggleClass('d-none');
            $('#internalcertificate-list').toggleClass('d-none');
        });
    });

    function edit_internalcertificate(e) {
        $('#internalcertificate-id').val($(e).data('certid'));
        $('#internalcertificate-title').val($(e).data('title'));
        $('#internalcertificate-completion-date').val($(e).data('completiondate'));
        $('#internalcertificate-location').val($(e).data('location'));
        $('#internalcertificate-speaker').val($(e).data('speaker'));
        $('#internalcertificate-attachment-current').val($(e).data('attachment'));

        $('#form-internalcertificate').toggleClass('d-none');
        $('#internalcertificate-list').toggleClass('d-none');
    }

    async function remove_internalcertificate(e) {
        if (confirm('Are you sure you want to delete this post?')) {
            try {
                const response = await fetch('/remove/work/certificate/{{ $empno }}/'+$(e).data('certid'), {
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

<div id="internalcertificate-list">
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
    <table class="table table-sm table-striped table-hover" id="internalcertificate-list-table">
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
                <td class="text-nowrap">{{ $list->ic_title }}</td>
                <td class="text-nowrap">{{ $list->ic_date }}</td>
                <td class="text-nowrap">{{ $list->ic_address }}</td>
                <td class="text-nowrap">{{ $list->ic_speaker }}</td>
                <td>
                    @if($list->ic_file)
                        <embed src="{{ '/file/get/certificate/'.$list->ic_file }}" style="max-width: 100%; height: 150px;">
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1"
                        data-certid="{{ $list->ic_id }}"
                        data-title="{{ $list->ic_title }}"
                        data-completiondate="{{ $list->ic_date }}"
                        data-location="{{ $list->ic_address }}"
                        data-speaker="{{ $list->ic_speaker }}"
                        data-attachment="{{ $list->ic_file }}"
                        onclick="edit_internalcertificate(this)">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm m-1" data-certid="{{ $list->ic_id }}" onclick="remove_internalcertificate(this)">Remove</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-outline-secondary btn-sm" onclick="edit_internalcertificate(this)">Add</button>
</div>

<form id="form-internalcertificate" enctype="multipart/form-data" name="form-internalcertificate" method="post" action="{{ config('app.url') }}/save/work/certificate" class="mb-3 d-none">
    @csrf
    <input type="hidden" name="employee-number" id="employee-number" value="{{ $empno }}">
    <input type="hidden" name="internalcertificate-id" id="internalcertificate-id" value="">
    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="internalcertificate-title" id="internalcertificate-title">
                <label for="internalcertificate-title">Title</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="date" class="form-control-plaintext border-bottom" name="internalcertificate-completion-date" id="internalcertificate-completion-date">
                <label for="internalcertificate-completion-date">Completion Date</label>
            </div>
        </div>
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="internalcertificate-location" id="internalcertificate-location">
                <label for="internalcertificate-location">Location of Event/Course</label>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="text" class="form-control-plaintext border-bottom" name="internalcertificate-speaker" id="internalcertificate-speaker">
                <label for="internalcertificate-speaker">Speaker</label>
            </div>
        </div>
        
        <div class="col-lg-auto">
            <div class="form-floating mb-3">
                <input type="file" class="form-control-plaintext border-bottom" name="internalcertificate-attachment" id="internalcertificate-attachment">
                <input type="hidden" name="internalcertificate-attachment-current" id="internalcertificate-attachment-current">
                <label for="internalcertificate-attachment">Attachment</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">Save</button>
    <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-edit-internalcertificate">Cancel</button>
</form>