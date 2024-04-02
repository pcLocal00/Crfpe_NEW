@php
$modal_title=($row)?'Edition période':'Justification d\'absence';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<style type="text/css">
    #uploader_start{
        display: none;
    }
</style>
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_session_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="form_presences" class="form">
    <div class="modal-body" id="modal_form_presences_body">
        <div data-scroll="true" data-height="auto">
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

            <!-- <input type="hidden" name="id" id="INPUT_SESSION_ID" value="{{ ($row)?$row->id:0 }}" /> -->
            <!-- begin::form -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Type de justification<span class="text-danger">*</span></label>
                        <!-- <input type="hidden" id="selected_af_id" value="{{ ($row)?$row->af_id:$af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $af_id }}"> -->
                        <select id="SelectTypeAbsent" name="type_absent" class="form-control form-control-sm select2">
                            <option value="">Sélectionnez</option>
                            <!-- <option value="Malade">Malade</option>
                            <option value="Accident">Accident</option>
                            <option value="Annulé">Annulé</option> -->
                            <?php foreach ($absence_types as $absence_type): ?>
                                <option value="{{ $absence_type->code }}">{{ $absence_type->name }}</option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div id="uploader">
                        <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                    </div>
                    <!-- <input type="file" name="test_file" id="test_file"> -->
                </div>
            </div>  
            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger cancel-justif" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <!-- <span
                id="BTN_SAVE_STAGE"></span> --></button>
    </div>
</form>
<!-- Form  : end -->
<script>
$(document).ready(function() {
    $('.select2').select2();

    // $("#tree_schedulecontacts").on("select_node.jstree", function(evt, data){
    //         console.log('ok');
    //         console.log(data);
    //         //selected node object: data.node;
    //     }
    // );
    $("#form_presences").on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        // console.log($('#test_file').files[0]);
        // console.log($('#uploader').plupload('getFiles')[0].getSource());
        var formData = new FormData();
        $('#uploader').plupload('getFiles').forEach(file => {
            // console.log(file.getNative());
            formData.append('attachments[]',file.getNative());
        });
        // console.log($('#uploader').plupload('getFiles')[0].files);
        // formData.append('attachments[]',$('#uploader').plupload('getFiles')[0].getNative());
        // formData.append('attachments',$('#uploader').plupload('getFiles')[0].files[0]);
        // formData.append('attachments','lorem');
        // console.log($('#uploader').plupload('getFiles')[0]);
        // console.log($('#SelectTypeAbsent').val());
        selected = $('#tree_schedulecontacts').jstree();
          schedules_data = {'state': 'Absent justifié' ,'member_id': $('#membersSelectFilter').val(), 'schedules': [],'type_absent':$('#SelectTypeAbsent').val()};
          formData.append('schedules_data[member_id]',$('#membersSelectFilter').val());
            let schedules = [];
          selected.get_selected(true).forEach((schedule) => {
             // schedules_data['schedules'].push(schedule.id);
             if(schedule.li_attr.name == 'schedule'){
                // formData.append('schedules_data[schedules][][schedule_id]',schedule.li_attr.schedule_id);
                // formData.append('schedules_data[schedules][][member_id]',schedule.li_attr.member_id);
                schedules.push({'schedule_id' : schedule.li_attr.schedule_id,'member_id' : schedule.li_attr.member_id})
            }
            formData.append('schedules_data[schedules]', JSON.stringify(schedules));
            formData.append('schedules_data[state]','Absent justifié');
            formData.append('schedules_data[type_absent]',$('#SelectTypeAbsent').val());
          });
          // formData.append('schedules_data[]',schedules_data);
          // console.log(schedules_data);
        $.ajax({
            type: 'POST',
            url: '/api/presences/editstate/' + schedules_data['member_id'],
            // data: schedules_data,
            data: formData,
            async: false,
            // mimeType:"multipart/form-data",
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(json, status) {
                _createJSTree(json);
            },
            error: function(error) {},
            complete: function(resultat, statut) {}
        }); 
        // $('#tree_schedulecontacts').jstree(true).refresh();
        $('button.cancel-justif').click()
        $('#formFilterPresences').submit()
    });
});
// ClassicEditor.create(document.querySelector("#description"))
//     .then(editor => {})
//     .catch(error => {});
// $('[data-scroll="true"]').each(function() {
//     var el = $(this);
//     KTUtil.scrollInit(this, {
//         mobileNativeScroll: true,
//         handleWindowResize: true,
//         rememberPosition: (el.data('remember-position') == 'true' ? true : false)
//     });
// });
/*$("#formStage").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_STAGE');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/stage',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_STAGE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_stage').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_STAGE');
                _showResponseMessage('error', 'Ouups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_STAGE');
                _reload_dt_stages();
            }
        });
        return false;
    }
});*/
// $('.datepicker').datepicker({
//     language: 'fr',
//     format: 'dd/mm/yyyy',
//     todayHighlight: true,
//     orientation: "bottom left",
//     templates: {
//         leftArrow: '<i class="la la-angle-left"></i>',
//         rightArrow: '<i class="la la-angle-right"></i>'
//     }
// });
// $('#nb_days').on("input", function() {
//     nb_days = this.value;
//     calculateHours(nb_days, 'nb_hours');
// });

// _loadAfsForSelectOptions('afsSelectStage');
// function _loadAfsForSelectOptions(select_id) {
//     var selected_af_id=$('#selected_af_id').val();
//     var default_af_id=$('#default_af_id').val();
//     _showLoader('LOADER_AFS');
//     $('#'+select_id).empty();     
//     $.ajax({
//         url: '/api/select/options/afs/' + default_af_id,
//         dataType: 'json',
//         success: function(response) {
//             var array = response;
//             if (array != '') {
//                 for (i in array) {
//                     $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
//                 }
//             }
//         },
//         error: function(x, e) {}
//     }).done(function() {
//         if (selected_af_id != 0 && selected_af_id != '') {
//             $('#' + select_id + ' option[value="' + selected_af_id + '"]').attr('selected', 'selected');
//         }
//         _hideLoader('LOADER_AFS');
//     });
// }
$(function() {
    $("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : '../upload.php',

        // User can upload no more then 20 files in one go (sets multiple_queues to false)
        max_file_count: 20,
        
        chunk_size: '1mb',

        // Resize images on clientside if we can
        resize : {
            width : 200, 
            height : 200, 
            quality : 90,
            crop: true // crop to exact dimensions
        },
        
        filters : {
            // Maximum file size
            max_file_size : '1000mb',
            // Specify what files to browse for
            mime_types: [
                {title : "Image files", extensions : "jpg,gif,png"},
                {title : "Zip files", extensions : "zip"},
                {title : "Pdf files", extensions : "pdf"}
            ]
        },

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
        flash_swf_url : '../../js/Moxie.swf',

        // Silverlight settings
        silverlight_xap_url : '../../js/Moxie.xap',
        init : {
            FileFiltered : function(up,file){
                // console.log(file.getSource());
                console.log(file.getNative());
            }
        }
    });


    // Handle the case when form was submitted before uploading has finished
    $('#form').submit(function(e) {
        // Files in queue upload them first
        if ($('#uploader').plupload('getFiles').length > 0) {

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
});
</script>