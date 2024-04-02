@php
    $modal_title=($row)?'Edition contact':'Ajouter un contact';
    $createdAt = $updatedAt = $deletedAt = '';
    if($row){
    $createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
    $updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
    $deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
    }
    $checkedMainContact = ($row && $row->is_main_contact===1)?'checked="checked"':'';
    $birth_date =$student_status_date='';
    if($row){
        if($row->birth_date!=null){
            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->birth_date);
            $birth_date = $dt->format('d/m/Y');
        }
        if($row->student_status_date!=null){
            $dts = Carbon\Carbon::createFromFormat('Y-m-d',$row->student_status_date);
            $student_status_date = $dts->format('d/m/Y');
        }
    }
@endphp
@csrf
<!-- <div id="CONTACT_FORM_BLOCK"></div> -->
<!-- begin::contact form -->
<div class="row">
    <div class="col-lg-3">
        <!-- Si particulier c'est un contact par défault -->
        <div class="form-group">
            @if($entity && $entity->entity_type=="S")
                <div class="checkbox-inline">
                    <label class="checkbox">
                        <input type="checkbox" value="1" name="c_is_main_contact" {{ $checkedMainContact }}>
                        <span></span>Principal</label>
                </div>
            @endif
            @if($entity && $entity->entity_type=="P")
                <input type="hidden" value="1" name="c_is_main_contact">
                <label class="text-primary">Principal</label>
            @endif
        </div>

    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <div class="checkbox-inline">
                @php
                    $checkedTraineeContact = ($row && $row->is_trainee_contact===1)?'checked="checked"':'';
                @endphp
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_trainee_contact" {{ $checkedTraineeContact }}>
                    <span></span>Stagiaire</label>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            @php
                $checkedBillingContact = ($row && $row->is_billing_contact===1)?'checked="checked"':'';
            @endphp
            <div class="checkbox-inline">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="c_is_billing_contact" {{ $checkedBillingContact }}>
                    <span></span>Facturation</label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            @php
                $checkedIsActive = ($row && $row->is_active==1)?'checked="checked"':'';
            @endphp
            <div class="checkbox-inline">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="c_is_active" {{ $checkedIsActive }} id="c_is_active">
                    <span></span>Actif</label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            @php
                $checkedIsValidAccounting = ($row && $row->is_valid_accounting===1)?'checked="checked"':'';
            @endphp
            <div class="checkbox-inline">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_valid_accounting" {{ $checkedIsValidAccounting }}>
                    <span></span>Validé Compta</label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <div class="checkbox-inline">
                @php
                    $checked = ($row && $row->is_former===1)?'checked="checked"':'';
                @endphp
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_former" {{ $checked }}>
                    <span></span>Formateur</label>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <div class="checkbox-inline">
                @php
                    $checked = ($row && $row->is_order_contact===1)?'checked="checked"':'';
                @endphp
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_order_contact" {{ $checked }}>
                    <span></span>Commande</label>
            </div>
        </div>
    </div>
</div>

<!-- begin::Type d'intervention si formateur -->
<div class="row">
    <div class="col-lg-12">
        <div class="form-group row" id="BLOCK_TYPE_INTERVENSION_FORMER">
            <label class="col-6 col-form-label">Type d’intervention du formateur <span
                    class="text-danger">*</span></label>
            <div class="col-6">
                <select class="form-control" name="c_type_former_intervention">
                    <option value="">Sélectionner un type</option>
                    @if($entity && $entity->entity_type=="S")
                        <option value="Sur facture"
                            {{ (($row && $row->type_former_intervention==='Sur facture')?'selected':'') }}>
                            Sur facture
                        </option>
                    @endif
                    @if($entity && $entity->entity_type=="P")
                        <option value="Sur contrat"
                            {{ (($row && $row->type_former_intervention==='Sur contrat')?'selected':'') }}>
                            Sur contrat
                        </option>
                        <option
                            value="Interne" {{ (($row && $row->type_former_intervention==='Interne')?'selected':'') }}>
                            Interne
                        </option>
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
<!-- end::Type d'intervention si formateur -->

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="genderSelects">Civilité</label>
            <select class="form-control " name="c_gender" id="genderSelects">
                <option {{ (($row && $row->gender==='M')?'selected':'') }} value="M">M</option>
                <option {{ (($row && $row->gender==='Mme')?'selected':'') }} value="Mme">Mme
                </option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Nom <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="c_lastname" id="c_lastname" value="{{ ($row)?$row->lastname:'' }}" required/>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Prénom <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="c_firstname" id="c_firstname" value="{{ ($row)?$row->firstname:'' }}"
                   required/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="c_email" id="c_email" value="{{ ($row)?$row->email:'' }}" required/>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="functionSelects">Fonction</label>
            <input type="text" class="form-control" name="c_function" value="{{ ($row)?$row->function:'' }}"/>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Téléphone pro </label>
            <input type="tel" class="form-control" name="c_pro_phone" value="{{ ($row)?$row->pro_phone:'' }}"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Portable pro </label>
            <input type="tel" class="form-control" name="c_pro_mobile" value="{{ ($row)?$row->pro_mobile:'' }}"/>
        </div>
    </div>
    <div class="col-lg-8">
        <label>Date de naissance </label>
        <div class="input-group date">
            <input type="text" class="form-control" name="birth_date" id="dateofbirth_datepicker"
                   placeholder="Sélectionner une date" value="{{ $birth_date }}" autocomplete="off"/>
            <div class="input-group-append">
                <span class="input-group-text">
                    <i class="la la-calendar-check-o"></i>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>Nom de naissance </label>
            <input type="text" class="form-control" name="birth_name" value="{{ ($row)?$row->birth_name:'' }}"/>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label>Département de naissance </label>
            <input type="text" class="form-control" name="birth_department" value="{{ ($row)?$row->birth_department:'' }}"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>Ville de naissance </label>
            <input type="text" class="form-control" name="birth_city" value="{{ ($row)?$row->birth_city:'' }}"/>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label>Nationalité </label>
            <input type="text" class="form-control" name="nationality" value="{{ ($row)?$row->nationality:'' }}"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>N° de sécurité social </label>
            <input type="text" class="form-control" name="social_security_number" value="{{ ($row)?$row->social_security_number:'' }}"/>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label>Code matricule </label>
            <input type="text" class="form-control" name="registration_code" value="{{ ($row)?$row->registration_code:'' }}"/>
        </div>
    </div>
</div>
<!--end::contact form-->
<div class="separator separator-dashed my-5"></div>
{{-- Begin student status  --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Statut de l'étudiant </label>
            <select name="student_status" class="form-control" id="student_status">
                <option value="">Aucun</option>
                <option value="student" {{($row && $row->student_status == 'student')? 'selected' : '' }}>Etudiant</option>
                <option value="apprentices" {{($row && $row->student_status == 'apprentices')? 'selected' : '' }}>Apprentis</option>
                <option value="employees" {{($row && $row->student_status == 'employees')? 'selected' : '' }}>Salariés</option>
                <option value="jobseeker" {{($row && $row->student_status == 'jobseeker')? 'selected' : '' }}>Demandeur d’emploi</option>
            </select>
        </div>
    </div>
    <div class="col-lg-6" id="BLOCK_STUDENT_STATUS_DATE">
        <label>Date de pris en compte du statut </label>
        <div class="input-group date">
            <input type="text" class="form-control" name="student_status_date" id="dateofbirth_consideration_date_status"
                   placeholder="Sélectionner une date" value="{{ $student_status_date }}" autocomplete="off"/>
            <div class="input-group-append">
                <span class="input-group-text">
                    <i class="la la-calendar-check-o"></i>
                </span>
            </div>
        </div>
    </div>
</div>
{{-- End student status --}}


<!-- Form contact : end -->
<script src="{{ asset('custom/js/form-contact.js?v=2') }}"></script>
<script type="text/javascript">
    checkRequerement();
    $('#c_is_active').on('change', function(e){
        checkRequerement();
    });
    function checkRequerement(){
        $("#c_firstname").attr("required", $('#c_is_active').prop('checked'));
        $("#c_lastname").attr("required", $('#c_is_active').prop('checked'));
        $("#c_email").attr("required", $('#c_is_active').prop('checked'));
    }
    $('#dateofbirth_consideration_date_status').datepicker({
        language: 'fr',
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
    _showStudentStatusDateBlock();
    function _showStudentStatusDateBlock() {
        var v = $('#student_status').val();
        if (v != '') {
            $('#BLOCK_STUDENT_STATUS_DATE').show();
            $("#dateofbirth_consideration_date_status").prop('required', true);
        } else {
            $('#BLOCK_STUDENT_STATUS_DATE').hide();
            $('#dateofbirth_consideration_date_status').val('');
            $("#dateofbirth_consideration_date_status").prop('required', false);
        }
    }
    $('#student_status').on('change', function() {
        _showStudentStatusDateBlock();
    });
</script>