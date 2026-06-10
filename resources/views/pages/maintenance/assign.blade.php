<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/i18n/defaults-*.min.js"></script>

<style type="text/css">
    .assign-item{
		border-radius: 3px;
		border: 1px solid lightgray;
		margin: 1px; 
		/* padding-left: 3px; */
		/* padding-right: 3px;  */
		display: block;
		/* height: 27px; */
		white-space: nowrap;
		overflow-x: hidden;
		font-size: 13px;
	}

	.assign-item:hover
	{
		background-color: lightblue;
		border: 1px solid blue;
	}

	.selecteditem{
		background-color: lightgreen;
		border: 1px solid green;
	}

	#assign-list{
		max-height: 500px;
		overflow-y: auto;
		zoom: .99;
		border: 1px solid gray;
		border-radius: 5px;
	}

    #assign-list .divassigngrp .card-header {
        font-size: 13px;
    }
</style>
<script type="text/javascript">
    let item = {
		elem : [],
		owner: null
	};

    $(function(){
        $.event.addProp('dataTransfer');

        $("#assign-type-search").on("keyup", function() {
		    var value = $(this).val().toLowerCase().split("+");
		    $("#assign-list .divassigngrp").filter(function() {
		    	var cntfnd = 0;
		    	for(x in value){
		    		cntfnd += ($(this).text().toLowerCase().indexOf(value[x].trim()) > -1 ? 1 : 0);
		    	}
		      	$(this).closest('.col').toggle(cntfnd > 0);
		    });
	  	});

        $("#assign-type-filter").change(function(){
            load_assign();
        });

        $('#modal-assign').on('shown.bs.modal', function(e){
            let btn = $(e.relatedTarget);
            
            $('#assign-emp').val('');
            $('#assign-type').val('');
            $('#assign-for').val('');
            $('#assign-remove').val('');

            $('.selectpicker').selectpicker('refresh');
        });

        $('#form-assign').submit(async function(e){
            e.preventDefault();

            $('#assign-err').html("");

            $("#btn-assignment-save").attr("disabled", true);
            
            let formData = new FormData();
            formData.append('emp', $('#assign-emp').val());
            formData.append('typeList', JSON.stringify($('#assign-type').val()));
            formData.append('assignment', $('#assign-for').val().join('|'));
            formData.append('remove', JSON.stringify($('#assign-remove').val()));

            let response = await fetch('/maintenance/assign/save', {
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
                load_assign();
            } else {
                $('#assign-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }

            $("#btn-assignment-save").attr("disabled", false);
        });


        $("#assign-list").on('click', '.assign-item', function(e){

            if(!item.owner)
            {
                item.owner = $(this).parents("div[data-draggable=\"target\"]").attr("data-id");
                $(this).addClass("selecteditem");
                item.elem.push(e.target);
            }else if(item.owner != $(this).parents("div[data-draggable=\"target\"]").attr("data-id")){
                $(".selecteditem").removeClass("selecteditem");
                item.elem = [];
                $(this).addClass("selecteditem");
                item.owner = $(this).parents("div[data-draggable=\"target\"]").attr("data-id");
                item.elem.push(e.target);
            }else if(item.owner == $(this).parents("div[data-draggable=\"target\"]").attr("data-id") && !$(this).hasClass("selecteditem")){
                $(this).addClass("selecteditem");
                item.elem.push(e.target);
            }else if($(this).hasClass("selecteditem")){
                $(this).removeClass("selecteditem");
                // item.elem = [];
                item.elem.splice( $.inArray(e.target, item.elem), 1 );
            }

        });

        $("#assign-list").on('dragstart', '.assign-item', function(e){

            if(!item.owner)
            {
                item.owner = $(this).parents("div[data-draggable=\"target\"]").attr("data-id");
                $(this).addClass("selecteditem");
                item.elem.push(e.target);
            }else if(item.owner != $(this).parents("div[data-draggable=\"target\"]").attr("data-id")){
                $(".selecteditem").removeClass("selecteditem");
                item.elem = [];
                $(this).addClass("selecteditem");
                item.owner = $(this).parents("div[data-draggable=\"target\"]").attr("data-id");
                item.elem.push(e.target);
            }else if(item.owner == $(this).parents("div[data-draggable=\"target\"]").attr("data-id") && !$(this).hasClass("selecteditem")){
                $(this).addClass("selecteditem");
                item.elem.push(e.target);
            }

            e.dataTransfer.setData('text', '');
        });

        $("#assign-list").on('dragover', '.divassigngrp > .card-body > [data-draggable="target"]', function(e){
            if(item.elem.length > 0)
            {
                e.preventDefault();
            }
        });

        $("#assign-list").on('drop', '.divassigngrp > .card-body > [data-draggable="target"]', function(e){
            e.preventDefault();
            if(e.target.getAttribute('data-draggable') == 'target' && e.target.getAttribute('data-id') != item.owner)
            {
                for(x in item.elem){
                    e.target.appendChild(item.elem[x]);
                }
                save_assignatory(item.owner, e.target.getAttribute('data-id'));

                $(".selecteditem").removeClass("selecteditem");

                item = {
                    elem : [],
                    owner: null
                };
            }
            else if($(e.target).hasClass("assign-item") && e.target.parentElement.getAttribute('data-id') != item.owner)
            {
                for(x in item.elem){
                    e.target.parentElement.appendChild(item.elem[x]);
                }
                save_assignatory(item.owner, e.target.parentElement.getAttribute('data-id'));

                $(".selecteditem").removeClass("selecteditem");

                item = {
                    elem : [],
                    owner: null
                };
            }
        });


        load_assign();
    });

    async function load_assign() {
        $('#assign-list').html('Loading...');
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/maintenance/assignment/list/' + $("#assign-type-filter").val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#assign-list').html(html);

            if($("#assign-type-search").val()){
                var value = $("#assign-type-search").val().toLowerCase().split("+");
			    $("#assign-list .divassigngrp").filter(function() {
			    	var cntfnd = 0;
			    	for(x in value){
			    		cntfnd += ($(this).text().toLowerCase().indexOf(value[x].trim()) > -1 ? 1 : 0);
			    	}
			      	$(this).closest('.col').toggle(cntfnd > 0);
			    });
		    }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function save_assignatory(src1, target1) {
		let empsrc = [];
		let emptarget = [];
		$("div[data-id=\"" + src1 + "\"] div.assign-item").each(function(){
			empsrc.push($(this).attr("data-empno"));
		});

		$("div[data-id=\"" + target1 + "\"] div.assign-item").each(function(){
			emptarget.push($(this).attr("data-empno"));
		});

        let formData = new FormData();
        formData.append('id', target1);
        formData.append('src', src1);
        formData.append('emparrsrc', JSON.stringify(empsrc));
        formData.append('emparrtarget', JSON.stringify(emptarget));
        formData.append('type', $('#assign-type-filter').val());

        let response = await fetch('/maintenance/assign/save', {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            }
        });

        let result = await response.json();

        if (response.ok && result.success) {
            alert('Saved');
        } else {
            $('#assign-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
        }
	}

    async function update_list(src1) {
		let empsrc = [];
		let emptarget = [];
		$(src1).parent().parent().find("div.assign-item").each(function(){
            if($(src1).parent().attr("data-empno") != $(this).attr("data-empno")){
                emptarget.push($(this).attr("data-empno"));
            }
        });

        let formData = new FormData();
        formData.append('id', $(src1).parent().parent().attr("data-id"));
        formData.append('src', '');
        formData.append('emparrsrc', JSON.stringify(empsrc));
        formData.append('emparrtarget', JSON.stringify(emptarget));
        formData.append('type', $('#assign-type-filter').val());

        let response = await fetch('/maintenance/assign/save', {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            }
        });

        let result = await response.json();

        if (response.ok && result.success) {
            $(src1).parent().remove();
            alert('Successfully removed');
        } else {
            $('#assign-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
        }
	}

    async function remove_assign(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/maintenance/assign/'+id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    alert('Removed');
                    load_assign();
                } else {
                    alert('Failed remove');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }
</script>

<div class="container-fluid">

    <div class="row justify-content-between">
        <div class="col-md-auto mb-1">
            <label for="assign-type-filter">Assignment Type:</label>
            <select id="assign-type-filter" class="ms-1">
                <option value="Time-off" selected>Time-off (LEAVE/OT/OFFSET)</option>
                <option value="DTR">DTR (WFH/EDTR/SCHEDULE)</option>
                <option value="Activities">Activities (TRAVEL/TRAINING)</option>
                <option value="PR">Personnel Request</option>
                <option value="PA">Performance Appraisal</option>
                <option value="GP">Gatepass</option>
                <option value="PRS">Permission Slip</option>
                <option value="RD">Rest Day</option>
            </select>
        </div>

        <div class="col-md-auto mb-1">
            <label for="assign-type-search">Assignment Type:</label>
            <input type="text" class="ms-1" id="assign-type-search" placeholder="Name1+Name2...">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-assign">New</button>
        </div>
    </div>
    <div id="assign-list" class="p-3 row row-cols-auto row-cols-md-5 gx-2"></div>
</div>

<div class="modal fade" id="modal-assign" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-assign-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-assign-label">Assignment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-assign">
                <div class="modal-body">
                    <div class="row" id="assign-err"></div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Employee:</labe>
                        <div class="col">
                            <select class="form-control form-control-sm selectpicker" data-live-search="true" title="Select" id="assign-emp">
                                @foreach ($arremp->where('status', 'Active') as $item)
                                    <option value="{{ $item['empno'] }}">{{ $item['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Assignment Type:</labe>
                        <div class="col">
                            <select class="form-control form-control-sm selectpicker" data-live-search="true" multiple data-actions-box="true" title="Select" id="assign-type">
                                <option value="Time-off" selected>Time-off (LEAVE/OT/OFFSET)</option>
                                <option value="DTR">DTR (WFH/EDTR/SCHEDULE)</option>
                                <option value="Activities">Activities (TRAVEL/TRAINING)</option>
                                <option value="PR">Personnel Request</option>
                                <option value="PA">Performance Appraisal</option>
                                <option value="GP">Gatepass</option>
                                <option value="PRS">Permission Slip</option>
                                <option value="RD">Rest Day</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Assigned For:</labe>
                        <div class="col">
                            <select class="form-control form-control-sm selectpicker" data-live-search="true" multiple data-actions-box="true" title="Select" id="assign-for">
                                @foreach ($arremp->where('status', 'Active') as $item)
                                    <option value="{{ $item['empno'] }}">{{ $item['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-4">Remove From:</labe>
                        <div class="col">
                            <select class="form-control form-control-sm selectpicker" data-live-search="true" multiple data-actions-box="true" title="Select" id="assign-remove">
                                @foreach ($arremp->where('status', 'Active') as $item)
                                    <option value="{{ $item['empno'] }}">{{ $item['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-assignment-save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>