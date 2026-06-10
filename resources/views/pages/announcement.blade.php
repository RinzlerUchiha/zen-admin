@extends('layouts.layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/@icon/themify-icons@1.0.6/themify-icons.min.css" rel="stylesheet">
<style>
    :root {
        --my-top-space: calc(var(--main-top-margin) + .25rem);
    }

    #page-tabs {
        width: 100%;
        height: calc(100vh - var(--my-top-space));
        max-height: calc(100vh - var(--my-top-space));
        flex-wrap: nowrap;
        /*transition: all 0.3s ease;*/
        position: sticky;
        top: var(--my-top-space);
        border-right: 1px solid lightgray;
        overflow: auto;
    }

    /* #page-tabs:hover {
        overflow: auto;
    } */

    #page-tabs li a {
        font-size: 12px;
        color: black;
    }

    #page-tabs li a.active {
        font-weight: bold;
        color: var(--bs-primary);
    }

    #page-tabs li:hover {
        background-color: #d1d1d1;
    }

    /* Adjusting scrollbar thickness */
    #page-tabs::-webkit-scrollbar {
        width: 7px;  /* Vertical scrollbar width */
        height: 7px; /* Horizontal scrollbar height */
    }

    /* Customize the scrollbar thumb (draggable part) */
    #page-tabs::-webkit-scrollbar-thumb {
        background: #8b8a8a;  /* Color of the thumb */
        border-radius: 10px;  /* Rounded corners for thumb */
    }

    .form-control-plaintext.border-bottom {
        padding-bottom: 1px !important;
    }

    fieldset:disabled select {
        -webkit-appearance: none; /* For Safari, Chrome */
        -moz-appearance: none;    /* For Firefox */
        appearance: none;         /* For modern browsers */
        /*background: transparent;*/  /* Make the background transparent if needed */
        border: none;             /* Optional: remove the border */
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0/html2canvas.min.js"></script>

<script type="text/javascript">
    $(function(){

        window.openPostOverlay = function(src) {
            document.getElementById('overlayImage').src = src;
            document.getElementById('imageOverlay').style.display = 'flex';
        }

        window.closeImageOverlay = function() {
            document.getElementById('imageOverlay').style.display = 'none';
        }
        
        const link_item = $("#page-tabs a[href='{{ config('app.url').'/announcement/'.$maincat }}']").parent()[0];
        if(link_item){
            // Scroll the item into view horizontally
            link_item.scrollIntoView({
                // behavior: 'smooth',  // Smooth scrolling
                block: 'center'     // Scroll the element to the center horizontally
            });
        }
    })
</script>

<style type="text/css">
    #post-desc {
        min-height: 50px;
    }

    #btn-emoji-list {
        line-height: 0;
    }

    button.emoji-item {
        line-height: normal;
    }

    #modal-post .form-select {
        padding-right: 1.5rem; /* less space on the right */
        background-size: 0.65em auto; /* shrink the caret icon */
    }

    .ann-post {
        font-size: 12px;
    }
    .ann-post-usr-img {
        height: 100%;
        min-height: 50px;
        width: 100%;
        max-width: 50px;
    }

    .ann-comment-usr-img {
        height: 35px;
    }

    .comment-list {
        font-size: 11px;
    }

    .comment-input * {
        background-color: white;
    }

    .comment-content:focus {
        outline: none;
        box-shadow: none;
    }

    .ann-post img {
        max-height: 50vh;
    }

    /* Fullscreen overlay */
    #imageOverlay {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
    }

    #imageOverlay img {
        object-fit: contain;
        max-width: 100%;
        max-height: 100%;
        {{-- border: 4px solid white; --}}
        {{-- border-radius: 10px; --}}
        {{-- box-shadow: 0 0 20px rgba(255, 255, 255, 0.2); --}}
    }

    #closeBtn {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 36px;
        color: white;
        cursor: pointer;
        z-index: 10000;
        background: transparent;
        border: none;
    }

    #closeBtn:hover {
        color: red;
    }



    .reaction {
        display: inline-block;
        padding: 8px;
        font-size: 24px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .reaction-container img,
    .reaction-list img{
        max-height: 30px;
        max-width: 30px;
        height: 30px;
        width: 30px;
    }

    i.ti,
    i.ti::before {
        padding: 0px;
        margin: 0px;
        vertical-align: middle;
        line-height: 1;
    }

    .reaction .img{
        max-height: 20px;
        max-width: 20px;
    }

    .reaction-container {
        position: relative;
        display: inline-block;
    }

    .reaction-trigger {
        /* text-decoration: none; */
        /* padding: 5px 10px; */
        padding: 0px;
        font-size: 20px; 
        height: fit-content;
        line-height: 1;

        /* border-radius: 5px; */
        cursor: pointer;
        display: inline-block;
    }

    .reaction-options {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 0;
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 30px;
        padding: 5px;
        box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
        overflow: auto;
        white-space: nowrap;
    }
</style>
<script type="text/javascript">
    let selectedImageFiles = [];
    let postOffset = 0;
    let isLoading = false;
    let isLastPost = false;

    $(function() {

        $(document)
            .on('click', '.reaction-trigger', async function () {
                $(this).siblings('.reaction-options').toggle();
            })
            .on('click', '.reaction-options .reaction', async function () {
                let reactionTrigger = $(this).closest('.reaction-container').find('.reaction-trigger');
                let reaction = $(this).data('reaction');

                if($(this).data('reaction') == $(this).closest('.reaction-container').find('.reaction-trigger').data('reaction')){
                    reaction = '';
                }

                let formData = new FormData();
                formData.append('post', $(this).closest('.ann-post').data('id') || '');
                formData.append('reaction', reaction);

                let response = await fetch('/announcement/reaction', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();
                console.log(result.reactions, Object.values(result.reactions).includes('like'));

                if (response.ok && result.success) {
                    $(this).closest('.reaction-options').hide();
                    $(this).closest('.reaction-container').parent().find('.reaction-list img').filter((_, el) => Object.values(result.reactions).includes($(el).data('reaction')) == false ).hide();

                    $(this).closest('.reaction-container').parent().find('.reaction-list img').filter((_, el) => Object.values(result.reactions).includes($(el).data('reaction')) == true ).show();

                    $(this).closest('.reaction-container').parent().find('.list-counter').text(result.reaction_cnt);
                    // reactionTrigger.hide();
                    reactionTrigger.data('reaction', reaction);
                    // reactionTrigger.html($(this).html() || '<i class="ti ti-face-smile"></i>').show();
                } else {
                    $('#post-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
                }
            });
        

        $('textarea').autoResize();

        $('#post-list').on('click', '.ann-content-image img', function () {
            const src = $(this).attr('src');
            $('#imageOverlay img').attr('src', src);
            $('#imageOverlay').css('display', 'flex').fadeIn();
            if($('#imageOverlay img').height() > $('#imageOverlay img').width()){
                $('#imageOverlay img').height('100%');
            }else{
                $('#imageOverlay img').width('100%');
            }
        });

        $('#closeBtn, #imageOverlay').on('click', function (e) {
            // Prevent closing when clicking on image
            if (e.target.tagName !== 'IMG') {
                $('#imageOverlay').fadeOut();
                $('#imageOverlay img').height('auto');
                $('#imageOverlay img').width('auto');
            }
        });

        $('#btn-emoji-list').click(function(){
            $('#emoji-list').toggle();
        });

        $('#post-list, #modal-view-comment').on('click', '.btn-emoji-list', function(){
            $(this).closest('.comment-area').find('.emoji-list').toggle();
        });

        $('#post-on').change(function(){
            if(this.value != 'now'){
                {{-- const now = new Date(); --}}
                {{-- const pad = n => String(n).padStart(2, '0'); --}}
                {{-- const formatted = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`; --}}
                $('#post-on-date').val('');
                $('#post-on-date').removeClass('d-none');
                $('#post-on-date').prop('required', true);
            }else{
                $('#post-on-date').addClass('d-none');
                $('#post-on-date').prop('required', false);
            }
        });

        $('#post-list, #modal-view-comment').on('click', '.emoji-list .emoji-item', function(){
            const $textarea = $(this).closest('.comment-area').find('.comment-content');
            const textarea = $textarea[0];

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = this.value;

            // Insert the text
            const original = $textarea.val();
            $textarea.val(original.substring(0, start) + text + original.substring(end)).trigger('input');

            // Set the cursor position
            textarea.selectionStart = textarea.selectionEnd = start + text.length;

            // Focus again
            textarea.focus();
        });

        $('#emoji-list .emoji-item').click(function(){
            const $textarea = $('#post-desc');
            const textarea = $textarea[0];

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = this.value;

            // Insert the text
            const original = $textarea.val();
            $textarea.val(original.substring(0, start) + text + original.substring(end)).trigger('input');

            // Set the cursor position
            textarea.selectionStart = textarea.selectionEnd = start + text.length;

            // Focus again
            textarea.focus();
        });

        $('#modal-post').on('shown.bs.modal', function(){
            selectedImageFiles = [];
        });

        $('#post-file').on('change', function (e) {
            const newFiles = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));

            newFiles.forEach((file) => {
                const index = selectedImageFiles.length; // track index for removal
                selectedImageFiles.push(file); // append to array

                const reader = new FileReader();
                reader.onload = function (e2) {
                const imgPreview = `
                    <div style="position: relative;" data-index="${index}">
                        <img src="${e2.target.result}" alt="Preview" style="max-width: 200px; border: 1px solid #ccc; border-radius: 5px;">
                        <button class="remove-single" style="position: absolute; top: 0; right: 0; background: red; color: white; border: none;">&times;</button>
                    </div>`;
                    $('#preview-post-file').append(imgPreview);
                };
                reader.readAsDataURL(file);
            });

            // Reset file input to allow re-uploading same file if needed
            $(this).val('');
        });

        $('#preview-post-file').on('click', '.remove-single', function () {
            const container = $(this).closest('[data-index]');
            const indexToRemove = parseInt(container.attr('data-index'));

            selectedImageFiles[indexToRemove] = null; // mark as null to preserve array index (can be filtered later)
            container.remove();
        });

        $("#form-post").submit(async function(e){
            e.preventDefault();
            $('#post-err').html("");

            const filesToSend = selectedImageFiles.filter(f => f !== null);
            // const files = $('#contract-file')[0].files;  // Get the selected files from the input

            // if($('#post-on').val() != 'now' && !$('#post-on-date').val()){
            //     $('#post-err').html("Please select date time");
            // }else if(!(filesToSend.length > 0 || $('#post-desc').val())){
            //     $('#post-err').html("Please attach file or input post");
            // }

            let formData = new FormData();
            formData.append('post-on', $('#post-on:visible').val() || 'datetime');
            formData.append('post-date', $('#post-on-date').val());
            formData.append('post-end-date', $('#post-end-date:visible').val() || '');
            formData.append('audience', $('#post-audience:visible').val() || '');
            formData.append('description', $('#post-desc').val());
            formData.append('type', $('#post-type').val());

            // Loop through each file and append it to the FormData object
            filesToSend.forEach((file, index) => {
                formData.append('files[]', file); // the name 'images[]' depends on your backend
            });

            let response = await fetch('/announcement/save', {
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
                window.location.reload();
            } else {
                $('#post-err').html(`<p style="color: red;">Error: ${result.error}</p>`);
            }
        });

        $('#post-list, #modal-view-comment').on('click', '.btn-send-comment', async function(){

            if($(this).closest('.comment-input').find('.comment-content').val().trim() == ''){
                alert('Please enter comment');
            }

            let formData = new FormData();
            formData.append('post', $(this).closest('.ann-post').data('id') || '');
            formData.append('comment', $(this).closest('.comment-input').find('.comment-content').val().trim());

            let response = await fetch('/announcement/comment/save', {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            });

            let result = await response.json();

            if (response.ok && result.success) {
                let newComment = '<div class="comment-list-item d-flex">';
                newComment += $(this).closest('.comment-area').find('.ann-comment-usr-img')[0].outerHTML;
                newComment += '<div class="d-block ms-1">';
                newComment += '<strong class="d-block post-user-name">' + result.name + '</strong>';
                newComment += '<p class="card-text m-0">' + $(this).closest('.comment-input').find('.comment-content').val().trim() + '</p>';
                newComment += '<span class="text-muted fw-light post-date d-block lh-sm">Just now</span>';
                newComment += '</div>';
                newComment += '</div>';
                $('.ann-post[data-id="' + $(this).closest('.ann-post').data('id') + '"]').find('.comment-list').append(newComment);
            } else {
                alert('Failed to post');
                console.log(`Error: ${result.error}`);
            }
        });

        $('#post-list').on('click', '.comment-count', function(){
            $('#modal-view-comment').modal('show');
            let post1 = $(this).closest('.ann-post').clone();
            post1.find('.comment-list-item').removeClass('d-none');
            $('#modal-view-comment .modal-body').html(post1);
        });

        loadPosts();
    });

    async function loadPosts() {
        if(isLoading || isLastPost) return;

        isLoading = true;
        $('#post-loader').show();
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/announcement/list/' + [$('#post-type').val(), postOffset].join('/'));
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#post-list').append(html);
            postOffset = $('#post-list .ann-post').length || 0;
            $('#post-loader').hide();
            if(html == ''){
                isLastPost = true;
            }
            isLoading = false;
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    async function deny_report(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/announcement/report/deny/'+id, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.ann-post[data-id="' + id + '"]').find('.ann-report').remove();
                } else {
                    alert('Failed process');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    function approve_report(id) {
        remove_post(id);
    }

    async function remove_post(id) {
        try {
            if (confirm("Are you sure?")) {

                let response = await fetch('/announcement/delete/'+[id, $('#post-type').val()].join('/'), {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    $('.ann-post[data-id="' + id + '"]').remove();
                    postOffset --;
                } else {
                    alert('Failed remove to post');
                    console.log(`Error: ${result.error}`);
                }
            }

        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    // Scroll detection
    $(window).on('scroll', function () {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
            loadPosts();
        }
    });


    function imageToJPEGBlob(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const reader = new FileReader();

            reader.onload = function (e) {
                img.src = e.target.result;
            };

            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);

                canvas.toBlob((blob) => {
                    const newFile = new File(
                        [blob],
                        file.name.replace(/\.\w+$/, '.jpg'), // Force .jpg extension
                        { type: 'image/jpeg' }
                    );
                    resolve(newFile);
                }, 'image/jpeg', 0.9); // quality 0.0 - 1.0
            };

            img.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
</script>
<div id="imageOverlay">
  <button id="closeBtn">&times;</button>
  <img src="" alt="Full Image">
</div>
<div class="row pt-1">
    <div class="col-md-2">
        <ul class="nav flex-column" id="page-tabs">
            <li class="nav-item"><a href="{{ config('app.url') }}/announcement" class="nav-link gap-2 icon-link {{$maincat == 'company' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/announcement.png') }}"> Company</a></li>
            <li class="nav-item"><a href="{{ config('app.url') }}/announcement/gov" class="nav-link gap-2 icon-link {{$maincat == 'gov' ? 'active' : ''}}"><img class="rounded-circle" width="30" height="30" src="{{ asset('icon/gov-ann.png') }}"> Government</a></li>
        </ul>
    </div>

    <div class="col-md-5 mx-auto">
        <input type="hidden" id="post-type" value="{{ $type }}">
        <div class="container-fluid px-0">
            <button class="btn btn-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modal-post">Post Announcement or Celebration?</button>
            <hr>
            <div id="post-list"></div>
            <div class="card placeholder-glow" aria-hidden="true" id="post-loader" style="display: none;">
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="img-fluid rounded-circle ann-post-usr-img placeholder"></div>
                        <div class="ms-1 d-block small flex-grow-1">
                            <span class="placeholder col-12"></span>
                            <span class="fw-light placeholder col-1"></span>
                        </div>
                    </div>
                    <p class="lh-sm small mb-3">
                        <span class="placeholder col-12"></span>
                        <span class="placeholder col-12"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-post" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-post-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-post-label">Create Post</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-post">
                <div class="modal-body">
                    <div class="row" id="post-err"></div>
                    <div class="row mb-3">
                        <labe class="col-form-label col-form-label-sm col-3">Post On:</labe>
                        <div class="col-auto">
                            <div class="d-flex">
                                <select id="post-on" class="form-select form-select-sm {{ $type == 'gov' ? 'd-none' : '' }}" style="width: fit-content;" {{ $type != 'gov' ? 'required' : '' }}>
                                    <option value="now">Now</option>
                                    <option value="datetime">Date Time</option>
                                </select>
                                <input style="width: fit-content;" type="{{ $type != 'gov' ? 'datetime-local' : 'date' }}" id="post-on-date" class="ms-1 form-control form-control-sm {{ $type != 'gov' ? 'd-none' : '' }}" value="{{ $type != 'gov' ? date('Y-m-d\TH:i') : date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 {{ $type != 'gov' ? 'd-none' : '' }}">
                        <labe class="col-form-label col-form-label-sm col-3">Post End:</labe>
                        <div class="col-auto">
                            <div class="d-flex">
                                <input style="width: fit-content;" type="date" id="post-end-date" class="ms-1 form-control form-control-sm" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 {{ $type == 'gov' ? 'd-none' : '' }}">
                        <labe class="col-form-label col-form-label-sm col-3">Post Audience:</labe>
                        <div class="col-9">
                            <select id="post-audience" class="form-select form-select-sm w-auto" required>
                                <option value="All">All</option>
                                @foreach($companyList as $c => $v)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                                <option value="Only Me">Only Me</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 mb-1">
                            <textarea id="post-desc" class="form-control form-control-sm" placeholder="{{ $type == 'gov' ? 'Post Information' : "What's on your mind?" }}" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="button" id="btn-emoji-list" class="btn btn-light btn-lg rounded-circle p-0 fs-5 float-end"><i class="fa-regular fa-face-smile"></i></button>
                            <div id="emoji-list" style="display: none;">
                                <ul class="nav nav-underline" id="emojiTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="face-tab" data-bs-toggle="tab" data-bs-target="#face-tab-pane" type="button" role="tab" aria-controls="face-tab-pane" aria-selected="true">&#128578;</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="heart-tab" data-bs-toggle="tab" data-bs-target="#heart-tab-pane" type="button" role="tab" aria-controls="heart-tab-pane" aria-selected="false">&#129293;</button>
                                        {{-- &#129293; or &#x1F90D; --}}
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="food-tab" data-bs-toggle="tab" data-bs-target="#food-tab-pane" type="button" role="tab" aria-controls="food-tab-pane" aria-selected="false">&#127860;</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="plant-tab" data-bs-toggle="tab" data-bs-target="#plant-tab-pane" type="button" role="tab" aria-controls="plant-tab-pane" aria-selected="false">&#127808;</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="weather-tab" data-bs-toggle="tab" data-bs-target="#weather-tab-pane" type="button" role="tab" aria-controls="weather-tab-pane" aria-selected="false">&#127759;</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="symbols-tab" data-bs-toggle="tab" data-bs-target="#symbols-tab-pane" type="button" role="tab" aria-controls="symbols-tab-pane" aria-selected="false">&#127881;</button>
                                    </li>
                                </ul>
                                <div class="tab-content pt-1" id="emojiTabContent">
                                    <div class="tab-pane fade show active" id="face-tab-pane" role="tabpanel" aria-labelledby="face-tab" tabindex="0">
                                        @foreach($emoji['faces'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="heart-tab-pane" role="tabpanel" aria-labelledby="heart-tab" tabindex="0">
                                        @foreach($emoji['heart'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="food-tab-pane" role="tabpanel" aria-labelledby="food-tab" tabindex="0">
                                        @foreach($emoji['food'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="plant-tab-pane" role="tabpanel" aria-labelledby="plant-tab" tabindex="0">
                                        @foreach($emoji['plant'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="weather-tab-pane" role="tabpanel" aria-labelledby="weather-tab" tabindex="0">
                                        @foreach($emoji['weather'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                    <div class="tab-pane fade" id="symbols-tab-pane" role="tabpanel" aria-labelledby="symbols-tab" tabindex="0">
                                        @foreach($emoji['symbols'] as $v)
                                            <button type="button" class="btn btn-light btn-sm rounded-circle p-0 fs-5 emoji-item" value="{!! $v !!}">{!! $v !!}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="file" accept="image/*" class="form-control form-control-sm" id="post-file" multiple>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="preview-post-file"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-post-announcement">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-view-comment" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modal-post-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
@stop