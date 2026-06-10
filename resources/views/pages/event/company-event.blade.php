<style type="text/css">
    #event-list {
        font-size: 12px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#company-event-image').on('change', function (e) {
            if(e.target.files.length > 0){
                const reader = new FileReader();
                reader.onload = function (e2) {
                const imgPreview = `<img src="${e2.target.result}" alt="Preview" style="max-width: 100%; border: 1px solid #ccc; border-radius: 5px;">`;
                    $('#preview-company-event-image').html(imgPreview);
                };
                reader.readAsDataURL(e.target.files[0]);
            }else{
                $('#preview-company-event-image').html('');
            }
        });

        $('#modal-company-event').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);console.log(btn.data('id'));
            
            $('#company-event-id').val(btn.data('id') || '');
            $('#company-event-title').val(btn.data('title') || '');
            $('#company-event-date').val(btn.data('event-date') || '');
            $('#company-event-post-start').val(btn.data('post-start') || '');
            $('#company-event-post-end').val(btn.data('post-end') || '');
        });

        $('#form-company-event').submit(async function(e){
            e.preventDefault();

            $('#company-event-err').html("");

            let formData = new FormData();
            formData.append('id', $('#company-event-id').val());
            formData.append('title', $('#company-event-title').val());
            formData.append('event-date', $('#company-event-date').val());
            formData.append('post-start-date', $('#company-event-post-start').val());
            formData.append('post-end-date', $('#company-event-post-end').val());
            if($('#company-event-image')[0].files.length > 0){
                formData.append('file', $('#company-event-image')[0].files[0]);
            }

            let response = await fetch('/events/company/save', {
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
                load_company_events();
            } else {
                $('#company-event-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        load_company_events();
    });

    async function load_company_events() {
        $('#event-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/events/company/list/');
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#event-list').html(html);

            $('#event-list > table').DataTable({
                scrollY: '55vh',
                scrollCollapse: true,
                lengthMenu: [50, 100, { label: 'All', value: -1 }],
                ordering: false
            });
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function remove_event(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/events/company/delete/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_company_events();
                } else {
                    alert('Failed remove to post');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>

<div class="container-fluid">
    <div class="d-flex">
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modal-company-event">New</button>
    </div>
    <div id="event-list"></div>
</div>

<div class="modal fade" id="modal-company-event" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-company-event-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-company-event-label">Company Event</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-company-event">
                <div class="modal-body">
                    <div class="row" id="company-event-err"></div>
                    <input type="hidden" id="company-event-id" value="">
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Title:</labe>
                        <div class="col-8">
                            <input type="text" id="company-event-title" class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Event Date:</labe>
                        <div class="col-auto">
                            <input type="date" id="company-event-date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Post Start Date:</labe>
                        <div class="col-auto">
                            <input type="date" id="company-event-post-start" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Post End Date:</labe>
                        <div class="col-auto">
                            <input type="date" id="company-event-post-end" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="file" accept="image/*" class="form-control form-control-sm" id="company-event-image">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="preview-company-event-image"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-post-company-event">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>