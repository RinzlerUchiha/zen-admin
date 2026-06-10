<style>
    #form-personal input,
    #form-personal select {
        font-size: 12px;
    }

    #form-personal .form-floating>label {
        font-size: 14px;
    }

    /*#form-personal .form-floating>label {
        display: inline-flex;
        align-items: center;
    }

    #form-personal .form-floating>.form-control:disabled~label::after, 
    #form-personal .form-floating>:disabled~label::after {
        background-color: var(--bs-body-bg);
    }

    #form-personal .form-floating>.form-control:disabled~label::after, 
    #form-personal .form-floating>:disabled~label::after {
        content: '\f023';
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 12px;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        margin: auto;
    }*/
</style>

<script type="text/javascript">
    $(function(){
        $('.select-province').change(function(){
            let select = $(this).closest('.row').find('.select-city');
            select.val('');
            select.find('option').not('[value=""]').hide();
            select.find('option[province="' + this.value + '"]').show();
        });

        $('.select-city').change(function(){
            let select = $(this).closest('.row').find('.select-barangay');
            select.val('');
            select.find('option').not('[value=""]').hide();
            select.find('option[city="' + this.value + '"]').show();
        });

        $('#btn-edit-personal').click(function(){
            $('#form-personal fieldset').prop('disabled', false);
            $('#form-personal [type="submit"]').toggleClass('d-none');
            $('#btn-cancel-personal').toggleClass('d-none');
            $(this).toggleClass('d-none');
        });

        $('#btn-cancel-personal').click(function(){
            $('#form-personal fieldset').prop('disabled', true);
            $('#form-personal #btn-edit-personal').toggleClass('d-none');
            $('#form-personal [type="submit"]').toggleClass('d-none');
            $(this).toggleClass('d-none');
        });


        
        let imgInput = document.getElementById('personal-img-input');
        let imgpreview = document.getElementById('personal-img-preview');

        imgpreview.addEventListener('click', () => imgInput.click());
        
        imgInput.addEventListener('change', () => {
            const file = imgInput.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('empno', document.getElementById('employee-number').value);

            fetch('/save/profile/img', {
                method: 'POST',
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    imgpreview.src = URL.createObjectURL(file);
                } else {
                    alert('Upload failed.');
                }
            })
            .catch(err => {
                console.error('Upload error:', err);
                alert('Upload error.');
            });
        });

    })
</script>

<div class="container-fluid">
    <form id="form-personal" class="mb-3" action="{{ config('app.url') }}/save/profile/personal" method="POST">
        <!-- Success Message -->
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
        @csrf
        <input type="hidden" name="employee-number" id="employee-number" value="{{ $empno }}">
        <input type="file" id="personal-img-input" accept="image/*" style="display:none;">
        <fieldset disabled>
            <div class="row g-3">
                <div class="col-lg-1">
                    <img id="personal-img-preview" src="{{ url("/")."/file/get/emp-img/".$empno }}" class="img-thumbnail img-fluid" alt="..." style="cursor: pointer;">
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-firstname" id="personal-firstname" value="{{ $empData->pers_firstname }}">
                        <label for="personal-firstname">First Name</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-middlename" id="personal-middlename" value="{{ $empData->pers_midname }}">
                        <label for="personal-middlename">Middle Name</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-lastname" id="personal-lastname" value="{{ $empData->pers_lastname }}">
                        <label for="personal-lastname">Last Name</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-suffix" id="personal-suffix" value="{{ $empData->pers_ext ?? '' }}">
                        <label for="personal-suffix">Suffix</label>
                    </div>
                </div>
            </div>
            
            <!-- contact -->
            <h6 class="mt-3">Contact Info</h6>
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control-plaintext border-bottom" name="personal-email" id="personal-email" value="{{ $empData->cont_email }}">
                        <label for="personal-email">Email</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-contact" id="personal-contact" value="{{ $empData->cont_person_num }}">
                        <label for="personal-contact">Personal Contact</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-company-contact" id="personal-company-contact" value="{{ $empData->cont_company_num }}">
                        <label for="personal-company-contact">Company Contact</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-telephone" id="personal-telephone" value="{{ $empData->cont_telephone }}">
                        <label for="personal-telephone">Telephone</label>
                    </div>
                </div>
            </div>

            <!-- address -->
            <h6 class="mt-3">Permanent Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="personal-padd-province" id="personal-padd-province" aria-label="">
                            <option value {{ !$empData->add_perm_prov ? 'selected' : '' }}>-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_name }}" {{ $empData->add_perm_prov == $list->pr_name ? 'selected' : '' }}>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-padd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="personal-padd-city" id="personal-padd-city" aria-label="">
                            <option value {{ !$empData->add_perm_city ? 'selected' : '' }}>-Select-</option>
                            @foreach($municipalityList as $list)
                                <option style="{{ $list->ct_province_name != $empData->add_perm_prov ? 'display: none;' : '' }}" province="{{ $list->ct_province_name }}" value="{{ $list->ct_name }}" {{ $empData->add_perm_city == $list->ct_name ? 'selected' : '' }}>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-padd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="personal-padd-barangay" id="personal-padd-barangay" aria-label="">
                            <option value {{ !$empData->add_perm_brngy ? 'selected' : '' }}>-Select-</option>
                            @foreach($barangayList as $list)
                                <option style="{{ $list->br_city_name != $empData->add_perm_city ? 'display: none;' : '' }}" city="{{ $list->br_city_name }}" value="{{ $list->br_name }}" {{ $empData->add_perm_brngy == $list->br_name ? 'selected' : '' }}>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-padd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-padd-specific" id="personal-padd-specific" value="{{ $empData->add_perm_location }}">
                        <label for="personal-padd-specific">Street/House #</label>
                    </div>
                </div>
            </div>

            <h6 class="mt-3">Current Address</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="personal-cadd-province" id="personal-cadd-province" aria-label="">
                            <option value {{ !$empData->add_cur_prov ? 'selected' : '' }}>-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_name }}" {{ $empData->add_cur_prov == $list->pr_name ? 'selected' : '' }}>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-cadd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="personal-cadd-city" id="personal-cadd-city" aria-label="">
                            <option value {{ !$empData->add_cur_city ? 'selected' : '' }}>-Select-</option>
                            @foreach($municipalityList as $list)
                                <option style="{{ $list->ct_province_name != $empData->add_cur_prov ? 'display: none;' : '' }}" province="{{ $list->ct_province_name }}" value="{{ $list->ct_name }}" {{ $empData->add_cur_city == $list->ct_name ? 'selected' : '' }}>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-cadd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="personal-cadd-barangay" id="personal-cadd-barangay" aria-label="">
                            <option value {{ !$empData->add_cur_brngy ? 'selected' : '' }}>-Select-</option>
                            @foreach($barangayList as $list)
                                <option style="{{ $list->br_city_name != $empData->add_cur_city ? 'display: none;' : '' }}" city="{{ $list->br_city_name }}" value="{{ $list->br_id }}" {{ $empData->add_cur_brngy == $list->br_name ? 'selected' : '' }}>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-cadd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-cadd-specific" id="personal-cadd-specific" value="{{ $empData->add_cur_location }}">
                        <label for="personal-cadd-specific">Street/House #</label>
                    </div>
                </div>
            </div>

            <h6 class="mt-3">Place Of Birth</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-province" name="personal-badd-province" id="personal-badd-province" aria-label="">
                            <option value {{ !$empData->add_birth_prov ? 'selected' : '' }}>-Select-</option>
                            @foreach($provinceList as $list)
                                <option value="{{ $list->pr_name }}" {{ $empData->add_birth_prov == $list->pr_name ? 'selected' : '' }}>{{ $list->pr_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-badd-province">Province</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-city" name="personal-badd-city" id="personal-badd-city" aria-label="">
                            <option value {{ !$empData->add_birth_city ? 'selected' : '' }}>-Select-</option>
                            @foreach($municipalityList as $list)
                                <option style="{{ $list->ct_province_name != $empData->add_birth_prov ? 'display: none;' : '' }}" province="{{ $list->ct_province_name }}" value="{{ $list->ct_name }}" {{ $empData->add_birth_city == $list->ct_name ? 'selected' : '' }}>{{ $list->ct_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-badd-city">City</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom select-barangay" name="personal-badd-barangay" id="personal-badd-barangay" aria-label="">
                            <option value {{ !$empData->add_birth_brngy ? 'selected' : '' }}>-Select-</option>
                            @foreach($barangayList as $list)
                                <option style="{{ $list->br_city_name != $empData->add_birth_city ? 'display: none;' : '' }}" city="{{ $list->br_city_name }}" value="{{ $list->br_id }}" {{ $empData->add_birth_brngy == $list->br_name ? 'selected' : '' }}>{{ $list->br_name }}</option>
                            @endforeach
                        </select>
                        <label for="personal-badd-barangay">Barangay</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-badd-specific" id="personal-badd-specific" value="{{ $empData->add_birth_location }}">
                        <label for="personal-badd-specific">Street/House #</label>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <h6 class="mt-3">Basic Info</h6>
            <div class="row g-3">
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control-plaintext border-bottom" name="personal-birthdate" id="personal-birthdate" value="{{ $empData->pers_birthdate }}">
                        <label for="personal-birthdate">Birth Date</label>
                    </div>
                </div>
                <div class="col-lg-1">
                    <div class="form-floating mb-3">
                        <input type="text" readonly class="form-control-plaintext border-bottom" id="personal-age" value="{{ $empData->age }}">
                        <label for="personal-age">Age</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="personal-civil-status" id="personal-civil-status" aria-label="">
                            <option value {{ !$empData->pers_civilstat ? 'selected' : '' }}>-Select-</option>
                            <option value="Single" {{ $empData->pers_civilstat == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ $empData->pers_civilstat == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Separated/Divorced" {{ $empData->pers_civilstat == 'Separated/Divorced' ? 'selected' : '' }}>Separated/Divorced</option>
                            <option value="Widow/Widower" {{ $empData->pers_civilstat == 'Widow/Widower' ? 'selected' : '' }}>Widow/Widower</option>
                        </select>
                        <label for="personal-civil-status">Civil Status</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="personal-sex" id="personal-sex" aria-label="">
                            <option value {{ !$empData->pers_sex ? 'selected' : '' }}>-Select-</option>
                            <option value="Male" {{ $empData->pers_sex == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $empData->pers_sex == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        <label for="personal-sex">Sex</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <select class="form-control-plaintext border-bottom" name="personal-bloodtype" id="personal-bloodtype" aria-label="">
                            <option value {{ !$empData->pers_bloodtype ? 'selected' : '' }}>-Select-</option>
                            <option value="O+" {{ $empData->pers_bloodtype == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ $empData->pers_bloodtype == 'O-' ? 'selected' : '' }}>O-</option>
                            <option value="A+" {{ $empData->pers_bloodtype == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ $empData->pers_bloodtype == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ $empData->pers_bloodtype == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ $empData->pers_bloodtype == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ $empData->pers_bloodtype == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ $empData->pers_bloodtype == 'AB-' ? 'selected' : '' }}>AB-</option>
                        </select>
                        <label for="personal-bloodtype">Blood Type</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-height" id="personal-height" value="{{ $empData->pers_height }}">
                        <label for="personal-height">Height (cm)</label>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-weight" id="personal-weight" value="{{ $empData->pers_weight }}">
                        <label for="personal-weight">Weight (kg)</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-religion" id="personal-religion" value="{{ $empData->pers_religion }}">
                        <label for="personal-religion">Religion</label>
                    </div>
                </div>
                <div class="col-lg-auto">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-dialect" id="personal-dialect" value="{{ $empData->pers_dialect }}">
                        <label for="personal-dialect">Dialect</label>
                    </div>
                </div>
            </div>

            <!-- gov accounts -->
            <h6 class="mt-3">Government Identification Numbers</h6>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-sss" id="personal-sss" value="{{ $empData->gov_sss }}">
                        <label for="personal-sss">SSS #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-hdmf" id="personal-hdmf" value="{{ $empData->gov_pagibig }}">
                        <label for="personal-hdmf">Pagibig #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-phic" id="personal-phic" value="{{ $empData->gov_philhealth }}">
                        <label for="personal-phic">Philhealth #</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control-plaintext border-bottom" name="personal-tin" id="personal-tin" value="{{ $empData->gov_tin }}">
                        <label for="personal-tin">TIN #</label>
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="d-flex justify-content-end mt-3 mb-5">
            <button type="button" id="btn-edit-personal" class="btn btn-outline-secondary btn-sm">Edit</button>
            <button type="button" id="btn-cancel-personal" class="btn btn-danger btn-sm mx-1 d-none">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm mx-1 d-none">Save</button>
        </div>
    </form>
</div>