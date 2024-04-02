@php
$modal_title='Création d’une pré planification';
$dtNow = Carbon\Carbon::now();
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_form_Preplanifications_title"><i class="flaticon-edit"></i> {{ $modal_title }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_Preplanifications_body">
    <div data-scroll="true" data-width="300">
        <form id="formPreplanifications" class="form">
            @csrf
            <input type="hidden" class="form-control" id="id" value="{{ $row->id ?? '' }}">
            <input type="hidden" class="form-control" id="PF_id" value="{{ $row->PF_id ?? '' }}">
            <!-- Begin:date -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h4>Titre du pré-planning : <span class="text-danger">*</span></h4>
                    <div class="form-group">
                        <input type="text" name="title" value="{{ $row->title ?? '' }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h4>Produit de référence : <span class="text-danger">*</span></h4>
                    <div class="form-group">
                        <label></label>
                        <select id="ProduitFormationOptions" name="pf_id_title" class="form-control form-control-sm select2" style="width: 620px !important"
                        @if($row_id > 0 && $row->Nb_Sessions > 0)
                            disabled
                        @endif
                    >
                            <option value="">--Produit de référence----</option>
                            <!-- add more options here -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="form-group">
                        <h4>Date de début : <span class="text-danger">*</span></h4>
                        <div class="input-group date">
                            @php
                            if($row_id == 0)
                            {
                                $start_date_db = $dtNow->format('d/m/Y');
                            }else{
                                $start_date_db = \Carbon\Carbon::parse($row->Start_date)->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="start_date"
                                id="start_date_datepicker" placeholder="Sélectionner une date" value="{{ $start_date_db }}"
                                autocomplete="off" required 
                                @if($row->Nb_Sessions > 0)
                                 disabled
                                @endif
                                />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End:date -->
            
            <!-- begin::tax discount -->
            
            <!-- end::tax discount -->

        </form>
    </div>
</div>
<div class="modal-footer justify-content-center">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formPreplanifications').submit();" id="btnajouter" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Créer le calendrier <span id="BTN_SAVE_Preplanifications"></span></button>
</div>

<script>


$(document).ready(function() {
    $('.select2').select2();
    if($('#id').val() > 0){
        $('#btnajouter').text("Mettre à jour");
    }
    var produitFormation = $('#formPreplanifications').find('input#id').val();
    //console.log(produitFormation);
});

$('#start_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

_loadProduitFormationOptions();

function _loadProduitFormationOptions() {
    var select_id = 'ProduitFormationOptions';
    var produitFormation = $('#formPreplanifications').find('input#id').val();
    var pf_id = $('#PF_id').val();
    
    //console.log(produitFormation);
    $.ajax({
        url: '/form/select/formation',
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    if(array[i].id == pf_id )
                    {
                        $('#' + select_id).append("<option value='" + array[i].id + "' selected>" + array[i].name +
                        "</option>");
                    }
                    $('#' + select_id).append("<option value='" + array[i].id + "' >" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
    });
}

var produitFormation = $('#formPreplanifications').find('input#id').val();
if(produitFormation == ''){produitFormation = 0};
var UrlPreplanning = '/form/Preplanifications/'+produitFormation;

$("#formPreplanifications").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_Preplanifications');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url:UrlPreplanning,
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_Preplanifications');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    //_formInvoice(result.invoice_id, result.af_id);
                    $('#modal_form_Preplanifications').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_Preplanifications');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_Preplanifications');
                if ($.fn.DataTable.isDataTable('#dt_produit_formation') || $.fn.DataTable.isDataTable(
                        '#dt_produit_formation')) {
                        _reload_dt_produit_formation();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});

</script>