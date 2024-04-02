@php
$modal_title=($row)?'Edition facture':'Créer une facture';

$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
$dtNow = Carbon\Carbon::now();
$af_id=$default_af_id;
$invoice_type='cvts_ctrs';
$funding_option='other_funders';
if($row){
    $af_id=($row->agreement)?$row->agreement->af_id:(($row->af_id>0)?$row->af_id:$default_af_id);
    $invoice_type=$row->invoice_type;
    $funding_option=$row->funding_option;
}

@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_invoice_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->

<div class="modal-body" id="modal_form_invoice_body">
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
        <form id="formInvoice" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_INVOICE_ID" name="id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="invoice_type" id="INPUT_HIDDEN_INVOICE_TYPE" value="{{ $invoice_type }}" />
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de facturation <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $bill_date =$dtNow->format('d/m/Y');
                            if($row && $row->bill_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->bill_date);
                            $bill_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="bill_date"
                                id="bill_date_datepicker" placeholder="Sélectionner une date" value="{{ $bill_date }}"
                                autocomplete="off" required {{($row)?'readonly':''}}/>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date d'échéance <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $newDateTime = Carbon\Carbon::now()->addMonth();
                            $due_date =$newDateTime->format('d/m/Y');
                            if($row && $row->due_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->due_date);
                            $due_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="due_date"
                                id="due_date_datepicker" placeholder="Sélectionner une date" value="{{ $due_date }}"
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
                        <label>AF <span id="LOADER_AFS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id" value="{{ $af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $default_af_id }}">
                        <select id="afsSelect" name="af_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    @if($invoice_type=='students')
                    @if($row->entity)
                    <p>Client : {{ $row->entity->name }} - {{ $row->entity->entity_type }} - ({{ $row->entity->ref }})
                    </p>
                    @endif
                    @else
                    <div class="form-group">
                        <label>Client <span id="LOADER_CLIENTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <select id="entitiesSelect" name="entitie_id" class="form-control form-control-sm select2"
                            required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            @if($invoice_type=='cvts_ctrs')
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Convention / Contrat <span id="LOADER_AGREEMENT"></span> <span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_agreement_id" value="{{ ($row)?$row->agreement_id:0 }}">
                        <select id="agreementsSelect" name="agreement_id" class="form-control form-control-sm select2"
                            required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    @if($invoice_type=='students')
                    @if($row->contact)
                    <p>Etudiant : {{ $row->contact->firstname }} {{ $row->contact->lastname }}</p>
                    @endif
                    @else
                    <div class="form-group">
                        <label>Contact <span id="LOADER_CONTACTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_contact_id" value="{{ ($row)?$row->contact_id:0 }}">
                        <select id="contactsSelect" name="contact_id" class="form-control form-control-sm select2"
                            required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cas : $funding_option == contact_itself, entity_contact, other_funders --}}
            <input type="hidden" name="funding_option" value="{{ ($row)?$row->funding_option:'other_funders' }}">
            @if(in_array($funding_option,['contact_itself','entity_contact']))
                @if(isset($funder_infos_array))
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-primary">Financeur : {{$funder_infos_array['name']}}</p>
                        <p class="text-primary">Contact de facturation (Financeur) : {{$funder_infos_array['contact_firstname']}} {{$funder_infos_array['contact_lastname']}}</p>
                    </div>
                </div>
                @endif
            @else
                <!-- funders -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Financeur <span id="LOADER_FUNDINGS"></span> <span class="text-danger">*</span></label>
                            <input type="hidden" id="selected_funding_id" value="{{ ($row)?$row->entitie_funder_id:0 }}">
                            <select id="fundersSelect" name="entitie_funder_id" class="form-control form-control-sm select2" required>
                                <option value="">Sélectionnez</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Contact de facturation (Financeur) <span id="LOADER_FUNDER_CONTACT"></span></label>
                            <input type="hidden" id="selected_contact_funder_id" value="{{ ($row)?$row->contact_funder_id:0 }}">
                            <select id="contactFunderSelect" name="contact_funder_id"
                                class="form-control form-control-sm select2" required>
                                <option value="">Sélectionnez</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            @if($invoice_type=='cvts_ctrs')
            <!-- funding payments : échéance-->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Echéance <span id="LOADER_ECHEANCE"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_fundingpayment_id"
                            value="{{ ($row)?$row->fundingpayment_id:0 }}">
                        <select id="echeancesSelect" name="fundingpayment_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif
            <!-- Les contacts financeurs -->
            <!-- <input type="hidden" name="entitie_funder_id" id="INPUT_HIDDEN_ENTITIE_FUNDER_ID" value="0"> -->
            
            

            <!-- funders -->

            <!-- Codes : de vente + analytique -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="accounting_code">Code comptable </label>
                        <input class="form-control " type="text" name="accounting_code"
                            value="{{ ($row)?$row->accounting_code:'' }}" id="accounting_code" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="analytical_code">Code analytique </label>
                        <input class="form-control " type="text" name="analytical_code"
                            value="{{ ($row)?$row->analytical_code:'' }}" id="analytical_code" />
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="collective_code">Code collectifs <span id="LOADER_CODE_COLLECTIVE"></span></label>
                        <input class="form-control " type="text" name="collective_code"
                            value="{{ ($row)?$row->collective_code:'' }}" id="collective_code" />
                    </div>
                </div>
            </div>
            
            <!-- begin :: note -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea class="form-control form-control-sm" id="note" name="note"
                            rows="3">{{ ($row)?$row->note:'' }}</textarea>
                    </div>
                </div>
            </div>
            <!-- end :: note -->

            @if($row)
            <!-- Items -->
            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionInvoiceItems">
                        <!-- Begin::elements -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseItems">
                                    <i class="flaticon-list"></i> Les élements
                                </div>
                            </div>
                            <div id="collapseItems" class="collapse show" data-parent="#accordionInvoiceItems">
                                <div class="card-body">
                                    @if($row->invoice_type=="students")
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button style="float:right;" type="button"
                                                class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                                title="Ajouter" onclick="_formInvoiceItem(0,{{$row->id}})"><i
                                                    class="flaticon2-add-1"></i></button>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-12" id="BODY_CARD_ITEMS"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Items -->
            @endif

            <div class="separator separator-dashed my-8"></div>
            <!-- begin::tax discount -->
            <h3>Tax et remise :</h3>
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <input type="hidden" id="selected_tax_1" value="{{ ($row)?$row->tax_percentage:'' }}">
                        <label>Taxe :</label>
                        <select id="taxesSelect" name="tax_percentage" class="form-control form-control-sm">
                            <option value="">--Pas de taxe----</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_label">Libellé de la remise :</label>
                        <input class="form-control form-control-sm" type="text" name="discount_label"
                            value="{{ ($row)?$row->discount_label:'' }}" id="discount_label" />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_amount">Montant de la remise :</label>
                        <input class="form-control form-control-sm" type="number" name="discount_amount"
                            value="{{ ($row)?$row->discount_amount:0 }}" id="discount_amount" />
                    </div>
                </div>
                @php
                $selectedPercent = ($row && $row->discount_amount_type ==='percentage')?'selected':'';
                $selectedFixed = ($row && $row->discount_amount_type ==='fixed_amount')?'selected':'';
                @endphp
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Mode de la remise : </label>
                        <select id="taxeOneSelect" name="discount_amount_type" class="form-control form-control-sm">
                            <option value="percentage" {{$selectedPercent}}>Pourcentage (%)</option>
                            <option value="fixed_amount" {{$selectedFixed}}>Montant fixe</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- end::tax discount -->
            
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formInvoice').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_INVOICE"></span></button>
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

$('#bill_date_datepicker, #due_date_datepicker').datepicker({
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
_loadAfsForSelectOptions('afsSelect', selected_af_id, default_af_id);

function _loadAfsForSelectOptions(select_id, selected_af_id, default_af_id) {
    _showLoader('LOADER_AFS');
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
        refreshAgreementsSelect();
        refreshEntitiesSelect();
        var invoice_type = $('#INPUT_HIDDEN_INVOICE_TYPE').val();
        if (invoice_type == 'students') {
            refreshEntityByAgreementSelect();
        }
    });
}
$('#afsSelect').on('change', function() {
    var af_id = $('#afsSelect').val();
    if (af_id > 0) {
        refreshEntitiesSelect();
    }
});
$('#entitiesSelect').on('change', function() {
    var entity_id = $('#entitiesSelect').val();
    if (entity_id > 0) {
        refreshAgreementsSelect();
    }
});

function refreshEntitiesSelect() {
    var af_id = $('#afsSelect').val();
    if (af_id > 0) {
        var selected_entity_id = $('#selected_entity_id').val();
        _loadEntitiesByAgreementForSelectOptions('entitiesSelect', af_id, selected_entity_id);
    }
}

function refreshContactsSelect() {
    var agreement_id = $('#agreementsSelect').val();
    if (agreement_id > 0) {
        var agreement_id = $('#agreementsSelect').val();
        var selected_contact_id = $('#selected_contact_id').val();
        _loadContactsByAgreementForSelectOptions('contactsSelect', agreement_id, selected_contact_id);
    }
}

function refreshAgreementsSelect() {
    var invoice_type = $('#INPUT_HIDDEN_INVOICE_TYPE').val();
    if (invoice_type == 'cvts_ctrs') {
        var entity_id = $('#entitiesSelect').val();
        if (entity_id > 0) {
            _showLoader('LOADER_AGREEMENT');
            var af_id = $('#afsSelect').val();
            var selected_agreement_id = $('#selected_agreement_id').val();
            _loadAgreementsForSelectOptions('agreementsSelect', af_id, entity_id, selected_agreement_id);
        }
    }
}

function _loadAgreementsForSelectOptions(select_id, af_id, entity_id, selected_agreement_id) {
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/agreements/' + af_id + '/' + entity_id + '/' + selected_agreement_id,
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
        if (selected_agreement_id != 0 && selected_agreement_id != '') {
            $('#' + select_id + ' option[value="' + selected_agreement_id + '"]').attr('selected', 'selected');
        }
        refreshEntityByAgreementSelect();
        refreshContactsSelect();
        _hideLoader('LOADER_AGREEMENT');
    });
}

function refreshEntityByAgreementSelect() {
    _loadFundungsByAgreementForSelectOptions('fundersSelect');
}

function refreshFunderContacts() {
    _loadContactsFunderByAgreementForSelectOptions('contactFunderSelect');
}
$('#agreementsSelect').on('change', function() {
    var agreement_id = $('#agreementsSelect').val();
    if (agreement_id > 0) {
        refreshEntityByAgreementSelect();
    }
});
$('#fundersSelect').on('change', function() {
    refreshEcheances();
    refreshFunderContacts();
});

function _loadEntitiesByAgreementForSelectOptions(select_id, af_id, selected_entity_id) {
    _showLoader('LOADER_CLIENTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/afentities/' + af_id + '/0',
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
        refreshAgreementsSelect();
        _loadCollectiveCodeFromEntity();
        _hideLoader('LOADER_CLIENTS');
    });
}
function _loadCollectiveCodeFromEntity() {
    var entity_id=$('#entitiesSelect').val();
    var invoice_id=$('#INPUT_HIDDEN_INVOICE_ID').val();
    //console.log(invoice_id);
    if(entity_id>0 && invoice_id==0){
        _showLoader('LOADER_CODE_COLLECTIVE');
        $.ajax({
            url: '/api/get/entity/collective_code/' + entity_id,
            dataType: 'json',
            success: function(response) {
                $('#collective_code').val(response.code);
            },
            error: function(x, e) {}
        }).done(function() {
            _hideLoader('LOADER_CODE_COLLECTIVE');
        });
    }
}

function _loadContactsByAgreementForSelectOptions(select_id, agreement_id, selected_contact_id) {
    _showLoader('LOADER_CONTACTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/entitycontact/' + agreement_id + '/C',
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

$("#formInvoice").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_INVOICE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/invoice',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_INVOICE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    _formInvoice(result.invoice_id, result.af_id);
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_INVOICE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_INVOICE');
                if ($.fn.DataTable.isDataTable('#dt_invoices_af') || $.fn.DataTable.isDataTable('#dt_invoices')) {
                    _reload_dt_invoices();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});

_loadItems();

function _loadItems() {
    var invoice_id = $('#INPUT_HIDDEN_INVOICE_ID').val();
    if (invoice_id > 0) {
        $('#BODY_CARD_ITEMS').html('<div class="spinner spinner-primary spinner-lg"></div>');
        $.ajax({
            url: '/get/invoice/items/' + invoice_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#BODY_CARD_ITEMS').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
}

/* function _formInvoiceItem(item_id, invoice_id) {
    var modal_id = 'modal_form_invoiceItem';
    var modal_content_id = 'modal_form_invoiceItem_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/agreement-item/' + item_id + '/' + invoice_id,
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
} */

function refreshEcheances() {
    _loadEcheancesSelectOptions('echeancesSelect');
}

function _loadEcheancesSelectOptions(select_id) {
    _showLoader('LOADER_ECHEANCE');
    var agreement_id = $('#agreementsSelect').val();
    var entity_id = $('#fundersSelect').val();
    //var entity_id = $('#entitiesSelect').val();
    var selected_id = $('#selected_fundingpayment_id').val();
    var invoice_id = $('#INPUT_HIDDEN_INVOICE_ID').val();
    var mode=(invoice_id>0)?2:1;
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/deadlines/' + entity_id+'/'+agreement_id+'/'+mode,
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
        if (selected_id != 0 && selected_id != '') {
            $('#' + select_id + ' option[value="' + selected_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_ECHEANCE');
    });
}

function _loadFundungsByAgreementForSelectOptions(select_id) {
    _showLoader('LOADER_FUNDINGS');
    $('#' + select_id).empty();
    var agreement_id = $('#agreementsSelect').val();
    var selected_funding_id = $('#selected_funding_id').val();
    var invoice_type = $('#INPUT_HIDDEN_INVOICE_TYPE').val();
    if (invoice_type == 'cvts_ctrs') {
        var url ='/api/select/options/fundings/' + agreement_id;
    }else{
        var url = '/api/select/options/listfundings';
    }
    $.ajax({
        url: url,
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
        if (selected_funding_id != 0 && selected_funding_id != '') {
            $('#' + select_id + ' option[value="' + selected_funding_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_FUNDINGS');
        refreshFunderContacts();
        if (invoice_type == 'cvts_ctrs') {
            refreshEcheances();
        }
    });
}

function _loadContactsFunderByAgreementForSelectOptions(select_id) {
    _showLoader('LOADER_FUNDER_CONTACT');
    $('#' + select_id).empty();
    var invoice_type = $('#INPUT_HIDDEN_INVOICE_TYPE').val();
    var entity_funder_id = $('#fundersSelect').val();
    var selected_contact_funder_id = $('#selected_contact_funder_id').val();
    if (invoice_type == 'cvts_ctrs') {
        var url ='/api/select/options/funder/contacts/' + entity_funder_id;
    }else{
        var url = '/api/select/options/funder/listcontacts/' + entity_funder_id;
    }
    $.ajax({
        url: url,
        dataType: 'json',
        success: function(response) {
            //$('#INPUT_HIDDEN_ENTITIE_FUNDER_ID').val(response.entitie_funder_id);
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_contact_funder_id != 0 && selected_contact_funder_id != '') {
            $('#' + select_id + ' option[value="' + selected_contact_funder_id + '"]').attr('selected','selected');
        }
        _hideLoader('LOADER_FUNDER_CONTACT');
    });
}

_loadTaxesOptions();

function _loadTaxesOptions() {
    var selected_taxe = $('#selected_tax_1').val();
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
function _formInvoiceItem(item_id, invoice_id) {
    var modal_id = 'modal_form_invoiceItem';
    var modal_content_id = 'modal_form_invoiceItem_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/invoice-item/' + item_id + '/' + invoice_id,
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
function _deleteInvoiceItem(item_id){
    var successMsg = "Votre element a été supprimée.";
    var errorMsg = "Votre element n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer cet element?";
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
                url: "/api/delete/item/invoice",
                type: "DELETE",
                data: {
                    item_id: item_id
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
                     KTApp.unblockPage();
                    _loadItems();
                    if ($.fn.DataTable.isDataTable('#dt_invoices')) {
                        _reload_dt_invoices();
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
}
</script>