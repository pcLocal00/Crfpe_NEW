@php
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_cert_session"><i class="flaticon-add"></i> Ajouter un  niveau </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formCertSession" class="form">
    <div class="modal-body" id="modal_form_cert_session_body">
        <div data-scroll="true" data-height="600">
            @csrf
            <!-- Tree MODULES -->
            <input type="hidden" id="VIEW_INPUT_AF_ID_HELPER" value="">
            <div id="structure_temporelle_tree_modal" class="tree-cert-pf"></div>
            <!-- END Tree MODULES -->
            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form  : end -->
<script>
    var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
    $('#structure_temporelle_tree_modal').jstree({
        "core": {
            "multiple": true,
            "themes": {
                "responsive": true
            },
            //"check_callback" : false,
            'data': {
                'url': function(node) {
                    return '/get/tree/timeaf/structure/'+af_id+"/0/1";
                },
                'data': function(node) {
                    return {
                        'parent': node.id
                    };
                }
            },
        },
        "checkbox": {
            "keep_selected_style" : true,
            "three_state": true, // to avoid that fact that checking a node also check others
            "whole_node" : true,  // to avoid checking the box just clicking the node
            "tie_selection" : true, // for checking without selecting and selecting without checking
        },
        "plugins": ["state", "checkbox"]
    });
    $('[name=filter_start], [name=filter_end]').datepicker({
        language: 'fr',
        rtl: KTUtil.isRTL(),
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });

    $('#structure_temporelle_tree_modal').on('changed.jstree', function (obj, selected) {
        $('input[name*=af_sessions]').val(0);
        $.each(selected.selected, function (index, elm) {
            const id = parseInt(elm);
            if (isNaN(id)) return true;
            $('input#af_sessions'+id).val(1);
        });
    });

    $('#formCertSession').submit(function (e) {
        const selected = $('#structure_temporelle_tree_modal').jstree('get_selected');
        e.preventDefault();
        $.ajax({
            url: '/form/saveCertSession/' + af_id,
            data: {pfs_ids: selected},
            type: 'POST',
            dataType: 'json',
            success: function(html) {
                var icon = 'error';
                var msgText = '';
                if (html.success) {
                    $('#modal_form_cert_sessions_content').html('');
                    $('#modal_form_cert_sessions').modal('hide');
                    var icon = 'success';
                    var msgText = 'Niveau ajouté avec succès.';
                    resfreshJSTreeTemporelle();
                } else {
                    var icon = 'error';
                    var msgText = 'Erreur lors d\'ajout: '+(html.message)+'.';
                }
                swal.fire({
                    text: msgText,
                    icon: icon,
                    buttonsStyling: false,
                    confirmButtonText: '<i class="far fa-times-circle"></i> Fermer',
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-light-primary"
                    }
                }).then(function() {});
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    });
    
    // function resfreshJSTreeTemporelle() {
    //     $('#structure_temporelle_tree').jstree(true).settings.core.data.url = '/get/tree/timeaf/structure/'+af_id+"/0";
    //     $('#structure_temporelle_tree').jstree(true).refresh();
    // }

    function ExpandCollapseAll(idTree, type) {
        if (idTree != '' && type != '') {
            var action = '';
            if (type == 'EXPAND') {
                action = 'open_all';
            } else if (type == 'COLLAPSE') {
                action = 'close_all';
            }
            if (action != '') {
                $('#' + idTree).jstree(action);
            }
        }
    }
</script>