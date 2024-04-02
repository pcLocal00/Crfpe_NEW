{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Pré planifications
            </h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_produit_formation()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-light-primary ml-1" data-toggle="tooltip" title="Ajouter"
            onclick="_formPreplanification(0)"><i class="flaticon2-add-1"></i> Création d’une pré planification</button>        
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        {{-- <x-filter-form type="Avoirs" /> --}}
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_produit_formation">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Etat</th>
                    <th>Produit</th>
                    <th>Date début</th>
                    <th>Nb Séances</th>
                    <th>AF cible</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<!--end::Card-->
{{-- <x-modal id="modal_form_avoir" content="modal_form_avoir_content" /> --}}
<x-modal id="modal_form_Preplanifications" content="modal_form_Preplanifications_content" />
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<!-- <script src="{{ asset('custom/js/list-agreements.js?v=1') }}"></script> -->
<script>
    $('.select2').select2();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('[data-toggle="tooltip"]').tooltip();
    
    var dtUrl = '/api/sdt/planifications';
    var table = $('#dt_produit_formation');
    // begin first table
    table.DataTable({
        language: {
            url: "/custom/plugins/datatable/fr.json"
        },
        responsive: true,
        processing: true,
        paging: true,
        ordering: false,
        serverSide: false,
        ajax: {
            url: dtUrl,
            type: 'POST',
            data: {
                pagination: {
                    perpage: 50,
                },
            },
        },
        lengthMenu: [5, 10, 25, 50],
        pageLength: 10,
        headerCallback: function(thead, data, start, end, display) {
            // thead.getElementsByTagName('th')[0].innerHTML = `
            //             <label class="checkbox checkbox-single">
            //                 <input type="checkbox" value="" class="group-checkable"/>
            //                 <span></span>
            //             </label>`;
        },
        columnDefs: [
            {
                targets: [0],
                width: '320px',
            },
            {
                targets: [1],
                width: '40px',
            },
            {
                targets: [3],
                width: '80px',
            },
            {
            targets: [ 4,5 ],
            width: '120px',
            className: 'dt-left',
            orderable: false,
        }],
    });

    var _reload_dt_produit_formation = function() {
        $('#dt_produit_formation').DataTable().ajax.reload();
    }

    

var _reset = function() {
    _reload_dt_produit_formation();
}

function _formPreplanification(planification_id) {
    //mode == 0, ajout ou edit depuis la page view formation produit parent
    //mode == 1, ajout edit

    //type == 0,edit
    //type == 1, ajout
    var modal_id = 'modal_form_Preplanifications';
    var modal_content_id = 'modal_form_Preplanifications_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/preplanification/' + planification_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

var _deletePlanification = function(planification_id) {
    var successMsg="Votre Pré planifications a été supprimé.";
    var errorMsg="Votre Pré planifications n\'a pas été supprimé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer la Pré planifications?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimez-le!",
        cancelButtonText: "Annuler"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/planification/"+ planification_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    _reload_dt_produit_formation();
                    KTApp.unblockPage();
                }
            });
        }
    });
}

function _viewPlanification(planification_id) {
    // window.open("/view/planification/" + planification_id, '_blank');
    window.location.href = "/view/planification/" + planification_id;
}
</script>
{{-- end Scripts Section --}}
@endsection