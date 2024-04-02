@php
$modal_title=($row)?'Edition utilisateur':'Ajouter un utilisateur';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp


<div class="modal-header">
    <h5 class="modal-title" id="modal_form_user_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form user : begin -->
<form id="formUser" class="form">
    <div class="modal-body" id="modal_form_user_body">
        <div data-scroll="true" data-height="600">
            @csrf
            @if($row)
            <!-- Infos date : begin -->
            <div class="form-group row">
                <div class="col-lg-12">
                    @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                        {{ $createdAt }}</span>@endif
                    @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                        {{ $updatedAt }}</span>@endif
                </div>
                @if($deletedAt)
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                        <div class="alert-icon"><i class="flaticon-warning"></i></div>
                        <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Infos date : end -->
            <div class="separator separator-dashed my-5"></div>
            @endif
            <input type="hidden" name="id" id="INPUT_USER_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="valcontact" id="INPUT_CONTACT_ID" value="{{ ($row)?$row->valcontact:0 }}" />
            <!-- begin::user form -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->active==1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="active" {{ $checkedIsActive }}>
                                <span></span>Actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Contact <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-control datatable-input" data-col-index="7" id="responsableSelect"
                                name="filter_responsable" required>
                                @if($row)
                                @php
                                $selected_type = ($row && $row->contact_id)?'selected':'';
                                @endphp
                                <option value="{{ $row->contact_id }}" {{ $selected_type }}>{{ $row->name }} {{ $row->lastname }}</option>
                                @endif

                                <option value="">Sélectionner un contact</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstname" name="name" value="{{ ($row)?$row->name:'' }}"
                            required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Prénom </label>
                        <input type="text" class="form-control" id="lastname" name="lastname" value="{{ ($row)?$row->lastname:'' }}"
                            required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="la la-envelope-o"></i></span>
                            </div>
                            <input type="email" class="form-control" id="email" name="email" value="{{ ($row)?$row->email:'' }}"
                                required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Identifiant <span class="text-danger">*</span> <button type="button"
                                onclick="_generateLogin()" data-toggle="tooltip" title="Générer un login"
                                class="btn btn-icon btn-outline-primary btn-sm" id="BTN_GERERATE_LOGIN"><i
                                    class="flaticon2-reload"></i></button></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="la la-user icon-lg"></i></span>
                            </div>
                            <input type="text" class="form-control" id="inputLogin" name="login"
                                value="{{ ($row)?$row->login:'' }}" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @php
                $requiredPassword = '';
                if(!$row){
                $requiredPassword = 'required';
                }
                @endphp
                <div class="col-lg-12">

                    <!-- Begin::Note -->
                    <div class="alert alert-custom alert-outline-info fade show mb-5" role="alert">
                        <div class="alert-icon"><i class="flaticon-warning"></i></div>
                        <div class="alert-text">Le mot de passe par defaut : <span class="text-warning">crfpe21*</span>
                        </div>
                        <div class="alert-close">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"><i class="ki ki-close"></i></span>
                            </button>
                        </div>
                    </div>
                    <!-- End::Note -->
                    <div class="form-group">
                        <label>Mot de passe @if(!$row)<span class="text-danger">*</span>@endif <button type="button"
                                onclick="_generateRandomPassword()" data-toggle="tooltip"
                                title="Générer un mot de passe" class="btn btn-icon btn-outline-primary btn-sm"
                                id="BTN_GERERATE_PASSWORD"><i class="flaticon2-reload"></i></button></label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="la la-star-of-life icon-lg"></i>
                                </span>
                            </div>
                            <input type="password" id="inputPassword" class="form-control" name="password"
                                oninput="_updateProgressBarPassword()" value="" {{ $requiredPassword }}>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <label class="checkbox checkbox-inline checkbox-primary">
                                        <input type="checkbox" data-toggle="tooltip" title="Afficher le mot de passe"
                                            onclick="_showPassword()">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                        </div>
                        @if($row)<span class="form-text text-muted">Si vous ne souhaitez pas le modifier, laissez
                            le champ vide.</span>@endif
                    </div>
                    <p class="mb-0" id="progress-bar-password"></p>
                </div>
            </div>

            <div class="separator separator-dashed my-5"></div>

            <h6 class="text-dark font-weight-bold mb-7">Profils et rôles:</h6>

            <!-- connected user roles -->
            @php
            $idsSelectedRolesConnectedUser=collect([]);
            if($row && Auth::user()->id==$row->id){
                $idsSelectedRolesConnectedUser=collect(Auth::user()->roles->pluck('code'));
            }
            @endphp
            <!-- connected user roles -->

            @if($profiles)
            @foreach($profiles as $profile)
            <div class="form-group row">
                <label class="col-form-label col-3 text-lg-right text-left">{{ $profile->name }}</label>
                <div class="col-9">
                    @if($roles)
                    @foreach($roles as $role)
                    @if($role->profil_id==$profile->id)
                    @php
                    $checkedRole=$disabledCheckbox='';
                    if($collectionRolesForUser->isNotEmpty()){
                    $checkedRole = ($collectionRolesForUser->contains($role->id))?'checked=checked':'';
                    }
                    if($idsSelectedRolesConnectedUser->isNotEmpty()){
                        if($role->code=='ADMIN'){
                            $disabledCheckbox = ($idsSelectedRolesConnectedUser->contains($role->code))?'disabled':'';
                        }
                    }
                    @endphp
                    <div class="checkbox-inline mb-2">
                        <label class="checkbox">
                            <input type="checkbox" {{ $checkedRole }} name="roles[]" value="{{ $role->id }}"
                                {{ $disabledCheckbox }}>
                            <span></span>{{ $role->name }}
                        </label>

                        <!-- if disabled  -->
                        @if($disabledCheckbox=='disabled')
                        <input type="hidden" name="roles[]" value="{{ $role->id }}">
                        <span class="text-danger"><i class="la la-ban"></i></span>
                        @endif
                        <!-- if disabled  -->

                    </div>
                    @endif
                    @endforeach
                    @endif
                </div>
            </div>
            @endforeach
            @endif

            <!--end::user form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"  onclick = "showResult();"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form user : end -->
<script src="{{ asset('custom/plugins/password-strength/password-strength.js?v=1') }}"></script>
<!-- <script src="{{ asset('custom/js/form-user.js?v=1') }}"></script> -->
<script>
var _updateProgressBarPassword = function() {
    var strength_password = calculate_strength_password('inputPassword');
    get_progress_bar_strength_password(strength_password, 'fr', 'progress-bar-password');
}

//_updateProgressBarPassword();

$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});

_loadcontacts('responsableSelect');
$('#responsableSelect').select2();

function _loadcontacts(select_id) {
    $.ajax({
        type: 'GET',
        url: 'api/select/options/getcontactswithnousers' ,
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {         
    });
}

$('#responsableSelect').change(function(){
    var id= $(this).val();
    $('#INPUT_CONTACT_ID').val(id);
    $.ajax({
        url: "api/select/options/getcontact/" + id,
        type: "GET",
        dataType: "JSON",
        success: function(result, status) {
            if(!!result["firstname"])
                $('#firstname').val(result["firstname"])
            else
                $('#firstname').val('')

            if(!!result["lastname"])
                $('#lastname').val(result["lastname"])
            else
                $('#lastname').val('')

            if(!!result["email"])
                $('#email').val(result["email"])
            else
                $('#email').val('')
        },
        error: function(result, status, error) {}
    });
})

var _showPassword = function() {
    var x = document.getElementById("inputPassword");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
var _generateRandomPassword = function() {
    _showLoader('BTN_GERERATE_PASSWORD');
    $.ajax({
        url: "/api/generate/password",
        type: "GET",
        dataType: "JSON",
        success: function(result, status) {
            $("#inputPassword").val(result.password);
            //Show password
            var x = document.getElementById("inputPassword");
            x.type = "text";
            //update progressbar
            _updateProgressBarPassword();
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
            $('#BTN_GERERATE_PASSWORD').html('<i class="flaticon2-reload"></i>');
        }
    });
}
var _generateLogin = function() {
    _showLoader('BTN_GERERATE_LOGIN');
    var user_id = $('#INPUT_USER_ID').val();
    $.ajax({
        url: "/api/generate/login/" + user_id,
        type: "GET",
        dataType: "JSON",
        success: function(result, status) {
            $("#inputLogin").val(result.login);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
            $('#BTN_GERERATE_LOGIN').html('<i class="flaticon2-reload"></i>');
        }
    });
}



$("#formUser").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        $.ajax({
            type: 'POST',
            url: '/form/user',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_user').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE');
                if ($.fn.DataTable.isDataTable('#dt_users')) {
                    _reload_dt_users();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }

});
</script>