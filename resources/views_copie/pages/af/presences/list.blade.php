@extends('layout.default')

@section('content')

<input type="hidden" name="id" id="VIEW_INPUT_AF_ID_HELPER" value="0">
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Gestion des presences</h3>
        </div>
    </div>
    <div class="card-body">
        <x-filter-form type="Presences" :datafilter='$datafilter' />
        <div class="row justify-content-md-center">
            <div class="col-lg-8">
                <div class="card card-custom card-border">
                    <div class="card-body p-3">
                        <div id="tree_schedulecontacts" class="tree-demo font-size-sm jstree jstree-1 jstree-default jstree-default-responsive jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="585" aria-busy="false"> 
                            <span style="display: block;text-align:center;">Selectionez un Etudiant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-modal id="modal_form_presence" content="modal_form_presence_content" />
<x-modal id="modal_form_presence_attachments" content="modal_form_presence_attachments_content" />

@endsection

@section('styles')
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="{{ asset('custom/plugins/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<script src="{{ asset('custom/plugins/plupload/plupload.full.min.js') }}"></script> 
<script src="{{ asset('custom/plugins/plupload/jquery.ui.plupload/jquery.ui.plupload.js') }}"></script> 

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function _initJsTreePlanning(api_url, data) {
        var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
        $("#tree_schedulecontacts").html(spinner);
        $.ajax({
            type: 'POST',
            url: api_url + data['member_id'],
            data: data,
            dataType: 'json',
            success: function(json, status) {
                _createJSTree(json);
            },
            error: function(error) {},
            complete: function(resultat, statut) {}
        });
    }

    function _initJsTreePlanning3(api_url, data, id_member) {

        var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
        $("#tree_schedulecontacts").html(spinner);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
        });

        $.ajax({
            type: 'POST',
            url: api_url + id_member,
            data: data,
            dataType: 'json',
            success: function(json, status) {},
            error: function(error) {},
            complete: function(resultat, statut) {}
        });

    }

    function _createJSTree(jsondata) {
        $("#tree_schedulecontacts").jstree('destroy');

        $('#tree_schedulecontacts').jstree({
            'core': {
                "multiple": true,
                "themes": {
                    "responsive": true
                },
                "progressive_render": true,
                'data': jsondata
            },
            "checkbox": {
                "three_state": true, // to avoid that fact that checking a node also check others
            },
            "plugins": ["state", "types", "wholerow", "checkbox"]
        });
        $('#tree_schedulecontacts').bind("ready.jstree", function() {
            // initializeSelections();
        }).jstree();

        $('#tree_schedulecontacts').bind("before_open.jstree", function() {
            $('[data-toggle="tooltip"]').tooltip();
        }).jstree();
    }


    $('#formFilterPresences').validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            var formData = $(form).serializeArray();
            // console.log(form.member_id.value);
            //var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
            //$("#tree_schedulecontacts").html(spinner);return false;
            //$('#tree_schedulecontacts').html(spinner);
            _initJsTreePlanning('/api/presences/schedules/', formData)
        //     $.ajax({
        //         type: 'POST',
        //         url: '/api/tree/schedules/' + af_id + '/withcontacts',
        //         data: formData,
        //         dataType: 'json',
        //         success: function(json, status) {
        //             $('#LOADER_SPINER_FILTER_SCHEDULE').html('');
        //             //console.log(json);
        //             _createJSTree(json);
        //             //$('#tree_schedulecontacts').jstree(true).refresh();
        //         },
        //         error: function(error) {},
        //         complete: function(resultat, statut) {}
        //     });
            return false;
        }
    });

    function handleFormSubmit(formData) {
        _initJsTreePlanning('/api/presences/schedules/export/', formData);
        $.ajax({
                type: 'GET',
                url: '/api/presences/schedules/exportget',
                dataType: 'json',
                success: function(json, status) {
                },
                error: function(error) {},
                complete: function(resultat, statut) {}
            });
        
    }

    $("#exportlistaf").click(function(event) {
        var formData = $('#formFilterPresences').serializeArray();
        handleFormSubmit(formData);
        event.preventDefault();
    });

    let type_absent = null;
    function _formJustification() {
        // schedules_data = {'state': state ,'member_id': $('#membersSelectFilter').val(), 'schedules': []};
        var modal_id = 'modal_form_presence';
        var modal_content_id = 'modal_form_presence_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        schedulecontact_id = $('#membersSelectFilter').val();
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/presence/' + schedulecontact_id,
            type: 'GET',
            async: false,
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            },
            error: function(result, status, error) {

            },
            complete: function(result, status) {

            }
        });

        return false;
    }


   function editState(state){ 
      if(state == 'Absent justifié'){
        _formJustification();
      }else{
          selected = $('#tree_schedulecontacts').jstree();
          let schedules = [];
          schedules_data = {'state': state ,'member_id': $('#membersSelectFilter').val(), 'schedules': []};
          selected.get_selected(true).forEach((schedule) => {
            if(schedule.li_attr.name == 'schedule')
                schedules.push({'schedule_id' : schedule.li_attr.schedule_id,'member_id' : schedule.li_attr.member_id})
                // schedules_data.schedules.push({'schedule_id' : schedule.li_attr.schedule_id,'member_id' : schedule.li_attr.member_id});
          });
          schedules_data.schedules.push(JSON.stringify(schedules)); 

          id_member = $('#membersSelectFilter').val();
          _initJsTreePlanning3('/api/presences/editstate/', schedules_data, id_member);

          $('#formFilterPresences').submit();
      }
      
    }

    function _getFormAttachments(schedules_id, member_id){
        var modal_id = 'modal_form_presence_attachments';
        var modal_content_id = 'modal_form_presence_attachments_content';
        // schedulecontact_id = $('#membersSelectFilter').val();
        $('#' + modal_id).modal('show');
        // $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/presence/attachments/' + schedules_id+'/'+member_id,
            type: 'GET',
            async: false,
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            },
            error: function(result, status, error) {

            },
            complete: function(result, status) {

            }
        });
    }
$(document).ready(function() {
    $('.select2').select2();
    $('#sessionsSelectFilter,#groupesSelectFilter').select2();
});

$('#afsSelectEstimate').change(element=>{
    var af_id = $('#afsSelectEstimate').val();
    _loadDatasGroupsForSelectOptions('groupesSelectFilter', af_id, 0);
});

function _loadDatasMembresForSelect(){
    $('#membersSelectFilter').empty();
    $('#membersSelectFilter').append('<option value="0">Tous les étudiants</option>');
    var af_id = $('#afsSelectEstimate').val();
    var grp_id = $('#groupesSelectFilter').val();
    $.ajax({
        url: '/api/select/options/presences/members/' + af_id+'/'+grp_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#membersSelectFilter').append("<option value='" + array[i].id + "'>" + array[i].firstname+' '+array[i].lastname +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        // if (selected_value != 0 && selected_value != '') {
        //     $('#membersSelectFilter' + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
        // }
    });
}

function _loadDatasGroupsForSelectOptions(select_id, af_id, selected_value = 0) {
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/groups/' + af_id,
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
        if (selected_value != 0 && selected_value != '') {
            $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
        }
    });
}

var selected_af_id = $('#selected_af_id').val();
var default_af_id = $('#default_af_id').val();
_loadAfEstimateForSelectOptions('afsSelectEstimate', selected_af_id, default_af_id);
function _loadAfEstimateForSelectOptions(select_id, selected_af_id, default_af_id) {
    // _showLoader('LOADER_AFS');
    $('#'+select_id).empty();     
    $.ajax({
        url: '/api/select/options/afs/' + default_af_id,
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
        if (selected_af_id != 0 && selected_af_id != '') {
            $('#' + select_id + ' option[value="' + selected_af_id + '"]').attr('selected', 'selected');
        }
        // _hideLoader('LOADER_AFS');
        // refreshEntitySelect();
    });
}
</script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script> 
@endsection
