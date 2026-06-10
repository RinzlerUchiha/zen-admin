<style>
    #signature-pad-wrapper:not(.show) {
        display: none !important;
    }

    @media (min-width: 768px) {

        #signature-pad-wrapper.show {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            margin: auto;
            z-index: 9999;
            /*display: flex;*/
            /*flex-direction: column;*/
            background: white;
            height: 100vh;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        #signature-pad-wrapper.show {
            display: none !important;
        }
    }

    .div-signature {
        /*min-height: 100px;*/
        width: 150px;
        position: relative;
        height: fit-content;
    }

    .div-signature svg {
        /*position: absolute;*/
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        display: block;
        width: 100%;
        height: 100%;
        overflow: unset;
    }
</style>

<div class="card" style="width: 8.5in; margin: auto;">
    <div class="card-header">
        <span class="float-end">
            <button onclick="view13A('{{ $_13a->{'13a_id'} }}')" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i></button>
        </span>
        <label>Letter of Reply</label>
    </div>
    <div class="card-body">
        <form class="form-horizontal" id="form-reply">
            <input type="hidden" id="reply-id" value="{{ $letter->{'13ar_id'} }}">
            <input type="hidden" id="reply-13a" value="{{ $letter->{'13ar_13aid'} }}">
            <fieldset>
                @if($letter->{'13ar_timestamp'})
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p>{{ date('F d, Y', strtotime($letter->{'13ar_timestamp'})) }}</p>
                    </div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p>
                            {{ $_13a->issuedby_dept_name . "/" . $_13a->issuedby_name_init }}
                            <br>{{ $_13a->issuedby_pos_name }}
                            <br>{{ $_13a->issuedby_company_name }}
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        @if($user_empno==$_13a->{'13a_to'})
                            <textarea class="form-control" id="reply_content">{{ $letter->{'13ar_reply'} }}</textarea>
                        @else
                            <p>{!! nl2br($letter->{'13ar_reply'}) !!}</p>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p>Respectfully,</p>
                        <div class="div-signature">{!! $letter->{'13ar_sign'} !!}</div>
                        <p>
                            {{ $_13a->to_name_init }}
                            @if($user_empno==$_13a->{'13a_to'})
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-sign" onclick="sign()">Sign</button>
                            @endif
                            <br>{{ $_13a->pos_name }}
                        </p>
                    </div>
                </div>

            </fieldset>

            @if($user_empno==$_13a->{'13a_to'})
                <center id="btn-reply-save" style="{{ ($letter->{'13ar_id'}!="" ? "display: none;" : "") }}"><button type="submit" class="btn btn-primary">Submit</button></center>
                <center id="btn-reply-edit" style="{{ ($letter->{'13ar_id'}=="" ? "display: none;" : "") }}"><button type="button" class="btn btn-success">Edit</button></center>
            @endif
        </form>

        {{-- <button onclick="view13A('{{ $_13a->{'13a_id'} }}')" class="btn btn-outline-secondary btn-sm">13A</button> --}}
    </div>
</div>


<div id="signature-pad-wrapper" class="signature-pad-wrapper d-flex flex-column">
    <div id="signature-pad" class="signature-pad flex-grow-1">
        <canvas id="signature-pad-canvas" class="signature-pad-canvas h-100 w-100"></canvas>
    </div>
    <div class="d-grid d-block">
        <div id="btn-for-sign" class="btn-group">
            <button type="button" class="btn btn-danger btn-lg rounded-0 fs-3" onclick="cancel_sign()">Cancel</button>
            <button type="button" class="btn btn-outline-secondary btn-lg rounded-0 fs-3" data-action="clear">Clear</button>
            <button type="button" class="btn btn-primary btn-lg rounded-0 fs-3" onclick="save_sign()">Save</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" defer></script>

<script>
    var canvas, signaturePad;

    function resizeCanvas() {
        if (canvas) {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
    }

    $(function(){

        canvas = document.getElementById("signature-pad").querySelector("canvas");
        if(canvas){
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            signaturePad.minWidth = 3;
            signaturePad.maxWidth = 3;
        }

        window.onresize = resizeCanvas;

        $('#btn-for-sign button[data-action="clear"]').click(function(){
            signaturePad.clear();
        });

        $('textarea').autoResize();

        $("#form-reply").submit(async function(e){
            e.preventDefault();
            $('#err-msg').html("");

            if(!$('#form-reply .div-signature').html()){
                alert("Please Sign");
            }else{

                let formData = new FormData();
                formData.append("id", $("#reply-id").val());
                formData.append("_13a", $("#reply-13a").val());
                formData.append("reply", $("#reply_content").val());
                formData.append("sign", $('#form-reply .div-signature').html());

                let response = await fetch('/grievance/reply/save', {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response.ok) {
                    alert('Saved');
                    viewLetterOfReply($("#reply-13a").val());
                } else {
                    let result = await response.json();
                    $('#err-msg').html(`<p style="color: red;">Error: ${result.message}</p>`);
                }
            }
        });

        $("#btn-reply-edit").click(function(){
            $("#form-reply fieldset").find("button, textarea").prop("disabled", false);
            $("#btn-reply-save").show();
            $("#btn-reply-edit").hide();
        });
    });

    function sign() {
        if ($(window).height() > $(window).width()) {
            alert("Please rotate phone to landscape");
        } else {
            $("body").addClass('overflow-hidden');
            $("#signature-pad-wrapper").addClass('show');

            $("#div_sign").css({"width": "100%", "height": "100vh"});
            $("#signature-pad").css({"width": "100%", "height": "90%"});
            resizeCanvas();
        }
    }

    function cancel_sign() {
        $("body").removeClass('overflow-hidden');
        $("#signature-pad-wrapper").removeClass('show');
    }

    function save_sign() {
        if(signaturePad.isEmpty()){
            alert("Please Sign");
        }else{
            $('#form-reply .div-signature').html(signaturePad.toSVG());
            $("body").removeClass('overflow-hidden');
            $("#signature-pad-wrapper").removeClass('show');
        }
    }
</script>