<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset('custom/plugins/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css') }}">
@php
$modal_title=($row)?'Fiche de stage':'Ajouter une proposition de stage attachments';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
$start_at_carbon =Carbon\Carbon::parse($row->started_at);
$ended_at_carbon =Carbon\Carbon::parse($row->ended_at);
$started_at = ($row->started_at)?Carbon\Carbon::parse($row->started_at)->format('d/m/Y'):'';
$ended_at = ($row->ended_at)?Carbon\Carbon::parse($row->ended_at)->format('d/m/Y'):'';
$total_hours = $start_at_carbon->diffInDays($ended_at_carbon);
$total_hours = $row->session->nb_hours;
}
@endphp
<div class="modal-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h5 class="modal-title" id="modal_form_session_title" style="font-size: 15px;width: 80%;"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
            </div>
            <div class="col-lg-6">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float: right;">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-2">
                <p style="margin:0px !important;color: green;">Statut: Stage {{$row->state}}</p>
            </div>
            <div class="col-lg-2">
                <p style="margin:0px !important;color: red;">Statut: Stage Valideé</p>
            </div>
            <div class="col-lg-5">
                <p style="margin:0px !important;color: green;">Date début: {{$started_at}} - Date fin: {{$ended_at}}</p>
            </div>
            <div class="col-lg-3">
                <p style="margin:0px !important;color: blue;">Durée totale: {{$total_hours}} heures</p>
            </div>
        </div>
    </div>
</div>
<!-- Form : begin -->

    <div class="modal-body" id="modal_form_session_body">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <p>Stagiaire: {{$row->member->contact->firstname}} {{$row->member->contact->lastname}}</p>
                </div>
                <div class="col-lg-4">
                    Nobre de Période: {{$row->session->nb_days}}
                </div>
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-lg-12">
                    <p>Action de formation: {{$row->session->title}}({{$row->session->code}})</p>
                </div> 
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-lg-8">
                    <p>Etablissement de formation: {{$row->representing_contact->entitie->name}}</p>
                </div> 
                <div class="col-lg-4">
                    <p>Référent forateur (Solaris): {{$row->trainer_referent_contact->firstname}} {{$row->trainer_referent_contact->lastname}}</p>
                </div> 
            </div>
            <div class="row" style="margin-top: 20px;">
                <fieldset style="width: 100%;border:1px solid;padding: 10px;">
                    <legend style="font-weight: bold">&nbsp;Organisme d'acceuil:&nbsp;</legend>
                    <div class="row">
                        <div class="col-lg-3">
                            <p>Nom: {{$row->entity->name}}</p>
                        </div>
                        <div class="col-lg-3">
                            <p>Référent de stage: {{$row->internship_referent_contact->firstname}} {{$row->internship_referent_contact->lastname}}</p>
                        </div>
                        <div class="col-lg-3">
                            <p>Représenté par: {{$row->representing_contact->firstname}} {{$row->representing_contact->lastname}}</p>
                        </div>
                        <div class="col-lg-3">
                            <p>Qualité du représentant: {{$row->representing_contact->function}}</p>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                         <div class="col-lg-3">
                            <p>Adresse: {{$row->adresse->line_1}}</p>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-lg-3">
                            <p>Nom: {{$row->entity->name}}</p>
                        </div>
                        <div class="col-lg-3">
                            <p>Service dans lequel le stage sera effectue: {{$row->service}}</p>
                        </div>
                        <div class="col-lg-2">
                            <p>Tél: {{$row->entity->fax}}</p>
                        </div>
                        <div class="col-lg-4">
                            <p>E-mail: {{$row->entity->email}}</p>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row justify-content-center" style="margin-top: 20px;">
            <div class="col-lg-11">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                    <button class="nav-link" id="periodes-tab" data-bs-toggle="tab" data-bs-target="#periodes" type="button" role="tab" aria-controls="periodes" aria-selected="true">Périodes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                    <button class="nav-link" id="conventions-tab" data-bs-toggle="tab" data-bs-target="#conventions" type="button" role="tab" aria-controls="conventions" aria-selected="false">Conventions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
                    </li>
                    <li class="nav-item" role="presentation">
                    <button class="nav-link" id="historiques-tab" data-bs-toggle="tab" data-bs-target="#historiques" type="button" role="tab" aria-controls="historiques" aria-selected="false">Historiques</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade" id="periodes" role="tabpanel" aria-labelledby="periodes-tab">...</div>
                    <div class="tab-pane fade" id="conventions" role="tabpanel" aria-labelledby="conventions-tab">...</div>
                    <div class="tab-pane fade show active" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                        <div class="row">
                            <h6 style="margin-top: 10px;">Documents</h6>
                            <div class="col-lg-12">
                                <div style="width:fit-content;float: right;">
                                    <form id="formUploadProposalStage" method="post" enctype="multipart/form-data">
                                        <div class="custom-file" style="width:70%;vertical-align: middle;">
                                            <input type="file" class="custom-file-input" name="attachments_stage[]" id="attachments_stage" multiple>
                                            <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
                                        </div>
                                        <button type="submit" form="formUploadProposalStage" class="btn btn-primary"><i class="fas fa-upload"></i></button>
                                    </form>
                                </div>
                                <div>
                                    <table class="table" id="table_stage_attachments">
                                      <thead>
                                        <tr>
                                          <th scope="col">Date</th>
                                          <!-- <th scope="col">Nom</th> -->
                                          <th scope="col">Image</th>
                                          <th scope="col">Action</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @if($medias)
                                            <?php foreach ($medias as $media): ?>
                                                <tr>
                                                  <td>{{$media->attachment->name}}</td>
                                                  <td>
                                                    <img width="20%" src="{{asset('uploads/stage/proposal/attachements/'.$media->attachment->path)}}">
                                                  </td>
                                                  <td><button onclick="_deleteAattachmentStage({{$media->attachment->id}}, this)" class="btn btn-sm btn-clean btn-icon"><i class="fas fa-minus-circle" style="color:red;"></i></button></td>
                                                </tr>
                                            <?php endforeach ?>
                                        @endif
                                       <!--  <tr>
                                          <td>Jacob</td>
                                          <td>Thornton</td>
                                          <td>@fat</td>
                                          <td><button class="btn btn-sm btn-clean btn-icon"><i class="fas fa-minus-circle" style="color:red;"></i></button></td>
                                        </tr> --> 
                                      </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- <div id="uploader">
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                            </div> -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="historiques" role="tabpanel" aria-labelledby="historiques-tab">...</div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="button" onclick="$('#formStageProposal').submit();" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_PROPOSAL"></span></button>
    </div>

<!-- Form  : end -->
<script>
$(document).ready(function() {
    let proposale_stage_id = {{$row->id ?? null}};
    //console.log(proposale_stage_id);
    $('.select2').select2();
    /* $("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "/form/stage/proposal/attachments/upload/"+proposale_stage_id,
 
        // Maximum file size
        max_file_size : '2mb',
 
        chunk_size: '1mb',
 
        // Resize images on clientside if we can
        resize : {
            width : 200,
            height : 200,
            quality : 90,
            crop: true // crop to exact dimensions
        },
 
        // Specify what files to browse for
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"},
            {title : "Zip files", extensions : "zip,avi"}
        ],
 
        // Rename files by clicking on their titles
        rename: true,
         
        // Sort files
        sortable: true,
 
        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,
 
        // Views to activate
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },
 
        // Flash settings
        flash_swf_url : '/plupload/js/Moxie.swf',
     
        // Silverlight settings
        silverlight_xap_url : '/plupload/js/Moxie.xap',
        headers: { 'Accept': 'application/json','X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    }); */
    $('#form').submit(function(e) {
        // Files in queue upload them first
        if ($('#uploader').plupload('getFiles').length > 0) {
            console.log('im here');
            // When all files are uploaded submit form
            $('#uploader').on('complete', function() {
                $('#form')[0].submit();
            });

            $('#uploader').plupload('start');
        } else {
            alert("You must have at least one file in the queue.");
        }
        return false; // Keep the form from submitting
    });


    $('#formUploadProposalStage').on('submit',function(e){
        e.preventDefault();
        
        let formData = new FormData($(this)[0]); 
        $.ajax({
            type: 'POST',
            url: "/form/stage/proposal/attachments/upload/"+proposale_stage_id,
            // data: schedules_data,
            data: formData,
            async: false,
            // mimeType:"multipart/form-data",
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(json, status) {
                // _createJSTree(json);
                if(json.files)
                    json.files.forEach(file => {
                            $('#table_stage_attachments').append(
                                '<tr>'+
                                    '<td>'+file.name+'</td>'+
                                    '<td><img width="20%" src="http://crfpe.local/uploads/stage/proposal/attachements/'+file.path+'"></td>'+
                                    '<td><button onclick="_deleteAattachmentStage('+file.id+')" class="btn btn-sm btn-clean btn-icon"><i class="fas fa-minus-circle" style="color:red;"></i></button></td>'+
                                '</tr>'
                                );
                    });
            },
            error: function(error) {},
            complete: function(resultat, statut) {}
        }); 
    });
});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formStageProposal").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_PROPOSAL');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/stage/proposal',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_stage_proposal').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                _showResponseMessage('error', 'Ouups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                _reload_dt_stage_proposals();
            }
        });
        return false;
    }
});
$('.datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

_loadAfsForSelectOptions('afsSelectStage');

function _deleteAattachmentStage(media_id, element){
    let that = element;
    $.ajax({
            type: 'POST',
            url: "/form/stage/proposal/attachments/delete/"+media_id,
            // data: schedules_data,
            async: false,
            // mimeType:"multipart/form-data",
            // cache: false,
            // contentType: false,
            // processData: false,
            dataType: 'json',
            success: function(json, status) {
                $(that).closest('tr').remove();
                // _createJSTree(json); 
            },
            error: function(error) {},
            complete: function(resultat, statut) {}
        });     
}

function _loadAfsForSelectOptions(select_id) {
    var selected_af_id = $('#selected_af_id').val();
    var default_af_id = $('#default_af_id').val();
    _showLoader('LOADER_AFS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/afs/' + default_af_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_af_id != 0 && selected_af_id != '') {
            $('#' + select_id + ' option[value="' + selected_af_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_AFS');
        _loadSessionsSelectStagePeriodsOptions('sessionsSelectStagePeriods');
        _loadMembersSelectStagePeriodsOptions('membersSelectStagePeriods');
        _loadEntitiesSelectStageOptions('entitiesSelectStage');
    });
}

function _loadSessionsSelectStagePeriodsOptions(select_id) {
    var af_id = $('#selected_af_id').val();
    var selected_session_id = $('#selected_session_id').val();
    var default_session_id = $('#default_session_id').val();
    _showLoader('LOADER_PERIODES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/sessions/periods/' + default_session_id + '/' + af_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_session_id != 0 && selected_session_id != '') {
            $('#' + select_id + ' option[value="' + selected_session_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_PERIODES');
    });
}

function _loadMembersSelectStagePeriodsOptions(select_id) {
    var af_id = $('#selected_af_id').val();
    var selected_member_id = $('#selected_member_id').val();
    var default_member_id = $('#default_member_id').val();
    _showLoader('LOADER_STAGIAIRES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stagiaires/members/' + default_member_id + '/' + af_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_member_id != 0 && selected_member_id != '') {
            $('#' + select_id + ' option[value="' + selected_member_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_STAGIAIRES');
    });
}

function _loadEntitiesSelectStageOptions(select_id) {
    var selected_entity_id = $('#selected_entity_id').val();
    var default_entity_id = $('#default_entity_id').val();
    _showLoader('LOADER_ENTITIES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/entities/' + default_entity_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_entity_id != 0 && selected_entity_id != '') {
            $('#' + select_id + ' option[value="' + selected_entity_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_ENTITIES');
        _loadContactsSelectStageOptions('representingContactsSelectStage', 1);
        _loadContactsSelectStageOptions('referentContactsSelectStage', 2);
        _loadContactsSelectStageOptions('trainerReferentContactsSelectStage', 3);
        _loadAdressesSelectStageOptions('adressesSelectStage');
    });
}
$('#entitiesSelectStage').on('change', function() {
    _loadContactsSelectStageOptions('representingContactsSelectStage', 1);
    _loadContactsSelectStageOptions('referentContactsSelectStage', 2);
    _loadAdressesSelectStageOptions('adressesSelectStage');
});

function _loadContactsSelectStageOptions(select_id, type) {
    /* 
    type==1 => Representant
    type==2 => Referent
    type==3 => formateur référent
     */
    if (type == 1) {
        var entity_id = $('#entitiesSelectStage').val();
        var selected_contact_id = $('#selected_representing_contact_id').val();
        var default_contact_id = $('#default_representing_contact_id').val();
        var loader_id = 'LOADER_REPS_CONTACTS';
    } else if (type == 2) {
        var entity_id = $('#entitiesSelectStage').val();
        var selected_contact_id = $('#selected_internship_referent_contact_id').val();
        var default_contact_id = $('#default_internship_referent_contact_id').val();
        var loader_id = 'LOADER_REFERENT_CONTACTS';
    } else if (type == 3) {
        var entity_id = 0; //formateurs
        var selected_contact_id = $('#selected_trainer_referent_contact_id').val();
        var default_contact_id = $('#default_trainer_referent_contact_id').val();
        var loader_id = 'LOADER_REFERENT_TRAINER_CONTACTS';
    }
    _showLoader(loader_id);
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/contacts/' + default_contact_id + '/' + entity_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_contact_id != 0 && selected_contact_id != '') {
            $('#' + select_id + ' option[value="' + selected_contact_id + '"]').attr('selected', 'selected');
        }
        _hideLoader(loader_id);
    });
}
function _loadAdressesSelectStageOptions(select_id) {
    var entity_id = $('#entitiesSelectStage').val();
    var selected_adresse_id = $('#selected_adresse_id').val();
    var default_adresse_id = $('#default_adresse_id').val();
    var loader_id = 'LOADER_ADRESSES';
    _showLoader(loader_id);
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/adresses/' + default_adresse_id + '/' + entity_id,
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
        if (selected_adresse_id != 0 && selected_adresse_id != '') {
            $('#' + select_id + ' option[value="' + selected_adresse_id + '"]').attr('selected', 'selected');
        }
        _hideLoader(loader_id);
    });
}

function _stage_proposal_get_referance(){
    member_id = $('#membersSelectStagePeriods').val();
    // select_id = $('#referentContactsSelectStage').val();
    select_id = 'referentContactsSelectStage';
    $.ajax({
        url: '/api/select/options/stage/referance/' + member_id,
        dataType: 'json',
        success: function(response) {
            console.log(response.firstname);
            $('#' + select_id).append("<option value='" + response.id + "'>" + response.firstname +"</option>");
            // var array = response;
            // if (array != '') {
            //     for (i in array) {
            //         $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
            //     }
            // }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_adresse_id != 0 && selected_adresse_id != '') {
            $('#' + select_id + ' option[value="' + selected_adresse_id + '"]').attr('selected', 'selected');
        }
        // _hideLoader(loader_id);
    });
}


</script>