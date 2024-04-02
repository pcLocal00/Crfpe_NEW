@php
$modal_title=($row)?'Edition contrat/convention':'Ajouter contrat/convention';

if($row && $row->agreement_type=='contract'){
    $modal_title='Edition contrat';
    $label='contrat';
}else{
    $modal_title='Ajouter convention';
    $label='convention'; 
}

$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_agreement_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->

<div class="modal-body" id="modal_form_agreement_body">
    <div data-scroll="true" data-height="600">

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
        <form id="formAgreement" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_AGREEMENT_ID" name="id" value="{{ ($row)?$row->id:0 }}" />
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de {{$label}} <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $agreement_date =$dtNow->format('d/m/Y');
                            if($row && $row->agreement_date!=null){
                                $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->agreement_date);
                                $agreement_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="agreement_date"
                                id="agreement_date_datepicker" placeholder="Sélectionner une date" value="{{ $agreement_date }}"
                                autocomplete="off" required {{($row)?'readonly':''}}/>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>AF <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id" value="{{ ($row)?$row->af_id:$default_af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $default_af_id }}">
                        <select id="afsSelectAgreement" name="af_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Client <span id="LOADER_CLIENTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <input type="hidden" id="default_entity_id" value="{{ $default_entity_id }}">
                        <select id="entitiesSelectAgreement" name="entitie_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Contact <span id="LOADER_CONTACTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_contact_id" value="{{ ($row)?$row->contact_id:0 }}">
                        <select id="contactsSelectAgreement" name="contact_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="hidden" id="selected_status" value="{{ ($row)?$row->status:'' }}">
                        <label>Statut <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" data-col-index="2" id="statusSelect" name="status"
                            required>
                            <option value="draft" {{ ($row && $row->status=='draft')?'selected':''}}>Brouillon</option>
                            <option value="sent" {{ ($row && $row->status=='sent')?'selected':''}}>Envoyé</option>
                            <option value="signed" {{ ($row && $row->status=='signed')?'selected':''}}>Signé</option>
                            <option value="canceled" {{ ($row && $row->status=='canceled')?'selected':''}}>Refusé
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="hidden" id="selected_tax_1" value="{{ ($row)?$row->tax_percentage:'' }}">
                        <label>Taxe 1</label>
                        <select id="taxesSelect" name="tax_percentage" class="form-control form-control-sm">
                            <option value="">--Pas de taxe----</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($row)
            <!-- Items -->
            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionEstimateItems">
                        <!-- Begin::elements -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseItems">
                                    <i class="flaticon-list"></i> Les élements
                                </div>
                            </div>
                            <div id="collapseItems" class="collapse show" data-parent="#accordionEstimateItems">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            @if(!$agreementHasInvoice)
                                            <button style="float:right;" type="button"
                                                class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                                title="Remise" onclick="_formDiscount({{$row->id}})"><i
                                                    class="flaticon2-percentage"></i></button>

                                            <button style="float:right;" type="button"
                                                class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                                title="Ajouter" onclick="_formAgreementItem(0,{{$row->id}})"><i
                                                    class="flaticon2-add-1"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" id="BODY_CARD_AGREEMENT_ITEMS"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Begin::Financements -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseItems2">
                                    <i class="flaticon-list"></i> Financements
                                </div>
                            </div>
                            <div id="collapseItems2" class="collapse" data-parent="#accordionEstimateItems">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" id="BODY_CARD_FUNDINGS"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::Financements -->

                    </div>
                </div>
            </div>
            <!-- Items -->
            @endif
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formAgreement').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_AGREEMENT"></span></button>
</div>

<!-- Form param : end -->
<!-- <script src="{{ asset('custom/js/form-estimate.js?v=1') }}"></script> -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});    
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$('#agreement_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
var selected_af_id = $('#selected_af_id').val();
var default_af_id = $('#default_af_id').val();
_loadAfEstimateForSelectOptions('afsSelectAgreement', selected_af_id, default_af_id);

function _loadAfEstimateForSelectOptions(select_id, selected_af_id, default_af_id) {
    //$('#'+select_id).empty();     
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
        refreshEntitySelect();
    });
}

function _loadEntitiesEstimateForSelectOptions(select_id, af_id, selected_entity_id, default_entity_id) {
    _showLoader('LOADER_CLIENTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/afentities/' + af_id + '/' + default_entity_id,
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
        _refreshSelectContacts();
        _hideLoader('LOADER_CLIENTS');
    });
}
$('#entitiesSelectAgreement').on('change', function() {
    _refreshSelectContacts();
});

function _refreshSelectContacts() {
    var entity_id = $('#entitiesSelectAgreement').val();
    var selected_contact_id = $('#selected_contact_id').val();
    _loadContactsSelectOptions('contactsSelectAgreement', entity_id, selected_contact_id);
}
$("#formAgreement").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_AGREEMENT');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/agreement',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_AGREEMENT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    var agreement_id = $('#INPUT_HIDDEN_AGREEMENT_ID').val();
                    if(agreement_id==0){
                        _formAgreement(result.agreement_id, result.af_id, result.entity_id);
                    }else{
                        $('#modal_form_agreement').modal('hide');
                    }
                    
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_AGREEMENT');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_AGREEMENT');
                if ($.fn.DataTable.isDataTable('#dt_agreements')) {
                    _reload_dt_agreements();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
$('#afsSelectAgreement').on('change', function() {
    var af_id = $('#afsSelectAgreement').val();
    if (af_id > 0) {
        
        var selected_entity_id = $('#selected_entity_id').val();
        var default_entity_id = $('#default_entity_id').val();
        _loadEntitiesEstimateForSelectOptions('entitiesSelectAgreement', af_id, selected_entity_id, default_entity_id);
    }
});

function refreshEntitySelect() {
    var agreement_id = $('#INPUT_HIDDEN_AGREEMENT_ID').val();
    var action_f_id = $('#selected_af_id').val();
    var selected_entity_id = $('#selected_entity_id').val();
    var default_entity_id = $('#default_entity_id').val();
    _loadEntitiesEstimateForSelectOptions('entitiesSelectAgreement', action_f_id, selected_entity_id, default_entity_id);
}
_loadItems();

function _loadItems() {
    var agreement_id = $('#INPUT_HIDDEN_AGREEMENT_ID').val();
    if (agreement_id > 0) {
        $('#BODY_CARD_AGREEMENT_ITEMS').html('<div class="spinner spinner-primary spinner-lg"></div>');
        $.ajax({
            url: '/get/agreement/items/' + agreement_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#BODY_CARD_AGREEMENT_ITEMS').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
}

function _formAgreementItem(item_id, agreement_id) {
    var modal_id = 'modal_form_agreementItem';
    var modal_content_id = 'modal_form_agreementItem_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/agreement-item/' + item_id + '/' + agreement_id,
        type: 'GET',
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

function _formDiscount(agreement_id) {
    $('#BLOCK_DISCOUNT').html('<div class="spinner spinner-primary spinner-lg"></div>');
    $.ajax({
        url: '/form/agreement/discount/' + agreement_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#BLOCK_DISCOUNT').html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
//
var selected_taxe = $('#selected_tax_1').val();
_loadTaxesOptions(selected_taxe);

function _loadTaxesOptions(selected_taxe) {
    var select_id = 'taxesSelect';
    $.ajax({
        url: '/api/select/taxes',
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
        if (selected_taxe != 0 && selected_taxe != '') {
            $('#' + select_id + ' option[value="' + selected_taxe + '"]').attr('selected', 'selected');
        }
    });
}

function _loadContactsSelectOptions(select_id, entity_id, selected_contact_id) {
    _showLoader('LOADER_CONTACTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/contacts/' + entity_id,
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
        _hideLoader('LOADER_CONTACTS');
    });
}

function annulateDiscount() {
    $('#BLOCK_DISCOUNT').html('');
}

/* FINANCEMENTS */
_loadFundings();

function _loadFundings() {
    var agreement_id = $('#INPUT_HIDDEN_AGREEMENT_ID').val();
    if (agreement_id > 0) {
        $('#BODY_CARD_FUNDINGS').html('<div class="spinner spinner-primary spinner-lg"></div>');
        $.ajax({
            url: '/get/agreement/fundings/' + agreement_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#BODY_CARD_FUNDINGS').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
}

function _formFunding(funding_id, agreement_id) {
    var modal_id = 'modal_form_funding';
    var modal_content_id = 'modal_form_funding_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/funding/' + funding_id + '/' + agreement_id,
        type: 'GET',
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

function _formFundingPayment(fundingpayment_id, funding_id) {
    var modal_id = 'modal_form_fundingpayment';
    var modal_content_id = 'modal_form_fundingpayment_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/fundingpayment/' + fundingpayment_id + '/' + funding_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}


function _deleteFunding(funding_id) {
    var successMsg = "Votre financeur a été supprimée.";
    var errorMsg = "Votre financeur n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer ce financeur?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/funding",
                type: "DELETE",
                data: {
                    funding_id: funding_id
                },
                dataType: "JSON",
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage("success", successMsg);
                    } else {
                        _showResponseMessage("error", errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error", errorMsg);
                },
                complete: function(result, status) {
                    _loadFundings();
                    KTApp.unblockPage();
                }
            });
        }
    });
}
function _deleteFundingPayment(fundingpayment_id) {
    var successMsg = "Votre ligne a été supprimée.";
    var errorMsg = "Votre ligne n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer cet ligne?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/fundingpayment",
                type: "DELETE",
                data: {
                    fundingpayment_id: fundingpayment_id
                },
                dataType: "JSON",
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage("success", successMsg);
                    } else {
                        _showResponseMessage("error", errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error", errorMsg);
                },
                complete: function(result, status) {
                    _loadFundings();
                    KTApp.unblockPage();
                }
            });
        }
    });
}
</script>