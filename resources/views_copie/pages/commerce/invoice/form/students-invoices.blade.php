@php
$modal_title='Créer des factures étudiants';
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_students_invoices_title"><i class="flaticon-edit"></i> {{ $modal_title }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
    <input type="hidden" value="{{json_encode($h_account_codes)}}" id="herited_account_codes">
    <input type="hidden" value="{{json_encode($h_analytic_codes)}}" id="herited_analytic_codes">
</div>
<div class="modal-body" id="modal_form_students_invoices_body">
    <div data-scroll="true" data-height="600">
        <form id="formStudentsInvoices" class="form">
            @csrf
            <!-- Begin:date -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de facturation <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $bill_date =$dtNow->format('d/m/Y');
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="bill_date"
                                id="bill_date_datepicker" placeholder="Sélectionner une date" value="{{ $bill_date }}"
                                autocomplete="off" required />
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
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="due_date"
                                id="due_date_datepicker" placeholder="Sélectionner une date" value="{{ $due_date }}"
                                autocomplete="off" required />
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
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>AF <span id="LOADER_AFS_SI"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id_si" value="{{ $af_id }}">
                        <select id="actionsformationsSelect" name="af_id" class="form-control form-control-sm select2"
                            required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="multipleStudentsSelect">Etudiants <span id="LOADER_STUDENTS_SI"></span><span
                                class="text-danger">*</span></label>
                        <select multiple="" class="form-control" id="multipleStudentsSelect" name="contacts_ids[]"
                            required>
                        </select>
                    </div>
                </div>
            </div>
            <h3>Financement :</h3>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Option de financeurs :</label>
                        <div class="radio-inline">
                            <label class="radio">
                                <input type="radio" name="funding_option" value="contact_itself" />
                                <span></span>
                                Le contact lui meme
                            </label>
                            <label class="radio">
                                <input type="radio" name="funding_option" value="entity_contact" />
                                <span></span>
                                L’entité du contact
                            </label>
                            <label class="radio">
                                <input type="radio" name="funding_option" checked="checked" value="other_funders" />
                                <span></span>
                                Autre financeur
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- funders -->
            <div id="block_funders"></div>
            <!-- due_date_funder -->
            <div class="col-md-6">
                <div class="form-group">
                    <label>Date d'échéance financeur <span class="text-danger">*</span></label>
                    <div class="input-group date">
                        <input type="text" class="form-control form-control-sm" name="due_date_funder"
                            id="due_date_funder_datepicker" placeholder="Sélectionner une date" value="{{ $due_date }}"
                            autocomplete="off" required />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="la la-calendar-check-o"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- begin :: codes : comptable (de vente) + analytique -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="accounting_code">Code comptable </label>
                        <input class="form-control " type="text" name="accounting_code" id="accounting_code" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="analytical_code">Code analytique </label>
                        <input class="form-control " type="text" name="analytical_code" id="analytical_code" />
                    </div>
                </div>
            </div>

            <!-- begin :: note -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea class="form-control form-control-sm" id="note" name="note" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <!-- end :: note -->
            <div class="row">
                <div class="col-md-12">
                    <h3>Les élements de facturation :</h3>
                    <button style="float:right;" type="button" class="btn btn-sm btn-icon btn-light-primary mr-2"
                        data-toggle="tooltip" title="Ajouter" onclick="_formAddItem()"><i
                            class="flaticon2-add-1"></i></button>
                </div>
            </div>

            <!-- begin:items -->
            <div class="separator separator-dashed my-8"></div>
            @isset($params)
            
            @foreach($params as $p)
                <input type="hidden" id="title_{{$p->id}}" value="{{$p->name}}">
                <input type="hidden" id="accounting_code_{{$p->id}}" value="{{$p->accounting_code}}">
                <input type="hidden" id="analytical_code_{{$p->id}}" value="{{$p->analytical_code}}">
                <input type="hidden" id="amount_{{$p->id}}" value="{{$p->amount}}">
            @endforeach

            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Element</th>
                                <th>description</th>
                                <th>Code comptable</th>
                                <th>Code analytique</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody id="TBODY_ELEMENTS">
                            <input type="hidden" value="0" id="last_element_id">
                            
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset
            <!-- end:items -->
            <div class="separator separator-dashed my-8"></div>
            <!-- begin::tax discount -->
            <h3>Tax et remise :</h3>
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Taxe :</label>
                        <select id="taxesSelectMinv" name="tax_percentage" class="form-control form-control-sm">
                            <option value="">--Pas de taxe----</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_label">Libellé de la remise :</label>
                        <input class="form-control form-control-sm" type="text" name="discount_label" value="Remise"
                            id="discount_label" />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_amount">Montant de la remise :</label>
                        <input class="form-control form-control-sm" type="number" name="discount_amount" value="0"
                            id="discount_amount" />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Mode de la remise : </label>
                        <select id="taxeOneSelect" name="discount_amount_type" class="form-control form-control-sm">
                            <option value="percentage" selected>Pourcentage (%)</option>
                            <option value="fixed_amount">Montant fixe</option>
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
    <button type="button" onclick="$('#formStudentsInvoices').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_STUDENTS_INVOICES"></span></button>
</div>
<script>
var herited_account_codes = JSON.parse($('#herited_account_codes').val());
var herited_analytic_codes = JSON.parse($('#herited_analytic_codes').val());
var overridden_account_codes = false;
var overridden_analytic_codes = false;
var _heritePfCodes = function() {
    const id_af = $("#actionsformationsSelect option:selected").val();
    var h_account_code = '', h_analytic_code = '';
    
    if (!overridden_account_codes) {
        if (herited_account_codes.hasOwnProperty(id_af)) {
            h_account_code = herited_account_codes[id_af];
        }
        $('[name=accounting_code]').val(h_account_code);
    }
    if (!overridden_analytic_codes) {
        if (herited_analytic_codes.hasOwnProperty(id_af)) {
            h_analytic_code = herited_analytic_codes[id_af];
        }
        $('[name=analytical_code]').val(h_analytic_code);
    }
}

$('[name=accounting_code]').on('change', function(e) {
    overridden_account_codes = true;
});
$('[name=analytical_code]').on('change', function(e) {
    overridden_analytic_codes = true;
});

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
$('#bill_date_datepicker,#due_date_datepicker,#due_date_funder_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
_loadAfsForSelectOptionsFormStudentsInvoices('actionsformationsSelect');

function _loadAfsForSelectOptionsFormStudentsInvoices(select_id) {
    var selected_af_id_si = $('#selected_af_id_si').val();
    var loaderId = 'LOADER_AFS_SI';
    _showLoader(loaderId);
    $.ajax({
        url: '/api/select/options/afs/' + selected_af_id_si,
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
        if (selected_af_id_si != 0 && selected_af_id_si != '') {
            $('#' + select_id + ' option[value="' + selected_af_id_si + '"]').attr('selected', 'selected');
        }
        _hideLoader(loaderId);
        _loadStudentdForSelectOptions('multipleStudentsSelect');
    });
}
$('#actionsformationsSelect').on('change', function() {
    _loadStudentdForSelectOptions('multipleStudentsSelect');
    _heritePfCodes();
});

function _loadStudentdForSelectOptions(select_id) {
    var af_id = $('#actionsformationsSelect').val();
    if (af_id > 0) {
        var loaderId = 'LOADER_STUDENTS_SI';
        _showLoader(loaderId);
        $.ajax({
            url: '/api/select/options/studentscontacts/' + af_id,
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
            _hideLoader(loaderId);
        });
    }
}
$("#formStudentsInvoices").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_STUDENTS_INVOICES');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/students/invoices',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_STUDENTS_INVOICES');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    //_formInvoice(result.invoice_id, result.af_id);
                    $('#modal_form_students_invoices').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_STUDENTS_INVOICES');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_STUDENTS_INVOICES');
                if ($.fn.DataTable.isDataTable('#dt_invoices_af') || $.fn.DataTable.isDataTable(
                        '#dt_invoices')) {
                    _reload_dt_invoices();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});

_loadTaxesMinvOptions();

function _loadTaxesMinvOptions() {
    var select_id = 'taxesSelectMinv';
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
    }).done(function() {});
}
$('input[name=funding_option]').on('change', function() {
    _load_block_funders();
});
_load_block_funders();

function _load_block_funders() {
    _showLoader('block_funders');
    var funding_option = $('input[name=funding_option]:checked').val();
    if (funding_option == "other_funders") {
        $.ajax({
            url: '/get/block/funders',
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#block_funders').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    } else {
        $('#block_funders').html('');
    }
}

function _formAddItem(){
    var i=$('#last_element_id').val();
    i++;
    $.ajax({
        url: '/form/param-to-item/'+i,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#last_element_id').val(i);
            $('#TBODY_ELEMENTS').append(html);
            _getItemInfosData(i);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
function _deleteItem(i){
    $('#TR_ID_'+i).remove();
}
function _onchangeSelectParams(i){
    _getItemInfosData(i);
}
function _getItemInfosData(i) {
    var id = $("#SELECT_PARAMS_"+i+" option:selected").val();
    if(id>0){
        var title=$("#title_"+id).val();
        var accounting_code=$("#accounting_code_"+id).val();
        var analytical_code=$("#analytical_code_"+id).val();
        var amount=$("#amount_"+id).val();
        $("#title_p_"+i).val(title);
        $("#accounting_code_p_"+i).val(accounting_code);
        $("#analytical_code_p_"+i).val(analytical_code);
        $("#rate_p_"+i).val(amount);
    }
}
</script>