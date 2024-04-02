{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="d-flex flex-column-fluid">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-md-12">
                <div class="card card-custom card-stretch">
                    <!--begin::Header-->
                    <div class="card-header py-1">
                        <div class="card-title align-items-start flex-column">
                           <!--  <h3 class="card-label font-weight-bolder text-dark">Aperçu</h3>
                            <span class="text-muted font-weight-bold font-size-sm mt-1"></span> -->
                        </div>
                        <div class="card-toolbar">
                            <a href="/form/email/new" class="btn btn-sm btn-icon btn-light-primary mb-0"
                                data-toggle="tooltip" title="Ajouter un modèle d'Email"><i class="flaticon2-add-1"></i></a>
                        </div>
                    </div>
                    <!--end::Header-->
                    <!-- <div class="card-body p-2">
                        
                    </div> -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <!--begin::Profile Card-->
                <div class="card card-custom card-stretch">
                    <!--begin::Body-->
                    <div class="card-body pt-4">
                        <!--begin::Nav-->
                        <div class="navi navi-bold navi-hover navi-active navi-link-rounded">
                            @if($emailmodels)
                            @foreach($emailmodels as $d)
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV{{$d['id']}}"
                                    onclick="_loadFormDocumentModel({{$d['id']}})" class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon-edit"></i>
                                    </span>
                                    <span class="navi-text font-size-lg">{{$d['name']}}</span>
                                </a>
                            </div>
                            @endforeach
                            @endif

                        </div>
                        <!--end::Nav-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Profile Card-->
            </div>
            <div class="col-lg-9" id="BLOCK_FORM_DOCUMENT">

            </div>
        </div>
    </div>
</div>

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=1') }}"></script>
<!-- <script src="{{ asset('custom/js/list-ptemplates.js?v=1') }}"></script> -->
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var _loadFormDocumentModel = function(document_model_id) {
    var block_id = 'BLOCK_FORM_DOCUMENT';
    KTApp.block('#' + block_id, {
        overlayColor: '#000000',
        state: 'danger',
        message: 'Veuillez patienter...'
    });
    $.ajax({
        url: '/form/email/' + document_model_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + block_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
            KTApp.unblock('#' + block_id);
        }
    });
    $(".css-af").each(function() {
        $(this).removeClass("active");
    });
    $('#NAV' + document_model_id).addClass("active");
}

var _restore_default_value = function(row_id) {
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    var successMsg = "Le modèle par défaut a été restauré.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le modèle d'émail par défaut?";
    var errorMsg = "Le modèle par défaut n\'a été restauré.";
    var urlRestore = "/api/restore/email/" + row_id;
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
                url: urlRestore,
                type: "GET",
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
                    _loadFormDocumentModel(row_id);
                    KTApp.unblockPage();
                }
            });
        }
    });
}
</script>
@endsection