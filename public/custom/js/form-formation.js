var _generateCodeFormation = function() {
    _showLoader('BTN_GERERATE_CODE_PF');
    datas = $("#categorie_tree").jstree("get_selected");
    var categorie_id = 0;
    var formation_id = $('#INPUT_ID_FORMATION').val();
    if (datas[0] > 0) {categorie_id = datas[0];}
    $.ajax({
        url: "/api/code/formation/"+formation_id+"/"+categorie_id,
        type: "GET",
        dataType: "JSON",
        success: function(result, status) {
                $("#ID_CODE_PF").val(result.code);
                //_hideLoader('BTN_GERERATE_CODE_PF');
                $('#BTN_GERERATE_CODE_PF').html('<i class="flaticon2-reload"></i>');
        },
        error: function(result, status, error) {
            //_hideLoader('BTN_GERERATE_CODE_PF');
            $('#BTN_GERERATE_CODE_PF').html('<i class="flaticon2-reload"></i>');
        },
        complete: function(result, status) {
            //_hideLoader('BTN_GERERATE_CODE_PF');
            $('#BTN_GERERATE_CODE_PF').html('<i class="flaticon2-reload"></i>');
        }
    });
};
$("#formFormation").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {

        //la categorie
        var category_id=0;
        datas = $("#categorie_tree").jstree("get_selected");
        if (datas[0] > 0) {
            category_id=datas[0];
            $("#categorie_id").val(datas[0]);
        }
        if(category_id==0){
            _showResponseMessage('error', 'Veuillez sélectionner une catégorie!!');
            _hideLoader('BTN_SAVE_FORMATION');
            return false;
        }
        //la structure temporelle
        if ( $( "#timestructures_tree" ).length ) {
            var rs = $("#timestructures_tree").jstree("get_selected");
            //console.log(rs[0]);return false;
            if (rs && rs.length > 0) { // Check if rs is defined and has at least one element
                const myArray = rs[0].split("S");
                if (myArray[1] > 0) {
                    $("#timestructure_id").val(myArray[1]);
                }
            }
            
        }
        _showLoader('BTN_SAVE_FORMATION');
        $.ajax({
            type: 'POST',
            url: '/form/formation',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                $('#modal_form_formation').modal('hide');
                //console.log(result);
                if (result.success) {
                     mode=$('#INPUT_HIDDEN_EDIT_MODE').val();
                    _hideLoader('BTN_SAVE_FORMATION');
                    _showResponseMessage('success', result.msg);


                    var mode=$('#INPUT_HIDDEN_EDIT_MODE').val();
                    //console.log(mode);
                    if(mode==0){
                        location.reload();
                    }
                    if(mode==1){
                        resfreshJSTreeStructures(result.pf_id);
                    }

                    //_formFormation(result.pf_id,mode);
                }
            //    resfreshJSTreeStructures($('#product_id').val());
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_FORMATION');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
        /*    complete: function(resultat, statut) {
                resfreshJSTreeStructures($('#product_id').val());
            }*/
        }).done(function() {
            _hideLoader('BTN_SAVE_FORMATION');
        //    resfreshJSTreeStructures($('#product_id').val());

            if ( $.fn.DataTable.isDataTable( '#kt_dt_formations' ) ) {
                _reload_dt_formation();
            }
        });

        return false;
    }

});
$('#categorie_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        //"check_callback" : false,
        'data': {
            'url': function(node) {
                return '/get/categories/'+$("#categorie_id").val();
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        //"keep_selected_style": false,
        "three_state": false, // to avoid that fact that checking a node also check others
        //"whole_node" : false,  // to avoid checking the box just clicking the node
        //"tie_selection" : true // for checking without selecting and selecting without checking
    },
    "plugins": ["state", "checkbox"]
});

//A la fin du chargement
$('#categorie_tree').bind("ready.jstree", function () {initializeSelections();}).jstree();
function initializeSelections(){
	var instance = $('#categorie_tree').jstree(true);
	instance.deselect_all();
    instance.select_node($("#categorie_id").val());

}
function ResetSelections(){
	var instance = $('#categorie_tree').jstree(true);
	 instance.deselect_all();
}
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});

//Nombre de jours (Théoriques)
$('#nb_days').on("input", function() {
    //console.log('v='+this.value);
    nb_days = this.value;
    calculateHours(nb_days,'nb_hours')
});


