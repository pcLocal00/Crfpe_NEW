{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container-fluid">


        @php
        $createdAt = $updatedAt = $deletedAt= '';
        if($formation){
        $createdAt = ($formation->created_at)?$formation->created_at->format('d/m/Y H:i'):'';
        $updatedAt = ($formation->updated_at)?$formation->updated_at->format('d/m/Y H:i'):'';
        $deletedAt = ($formation->deleted_at)?$formation->deleted_at->format('d/m/Y H:i'):'';
        }
        @endphp
        <input type="hidden" id="VIEW_INPUT_PF_ID_HELPER" value="{{ $formation->id }}" />
        <!-- begin::card infos -->
        <div class="card card-custom gutter-b">
            <div class="card-body">
                <div class="d-flex">
                    <!--begin: Info-->
                    <div class="flex-grow-1">
                        <!--begin: Title-->
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div class="mr-3">
                                <!--begin::Name-->
                                <p class="text-muted mb-0">{{ $formation->code }}</p>
                                <a href="#"
                                    class="d-flex align-items-center text-dark text-hover-primary font-size-h5 font-weight-bold mr-3">{{ $formation->title }}
                                </a>
                                <!--end::Name-->
                                <!--begin::Contacts-->
                                <div class="d-flex flex-wrap my-2">


                                    @foreach($formationParams as $param_code=>$name)

                                    @php
                                    $label='';
                                    if($param_code=='PF_TYPE_FORMATION'){
                                    $label='Type : ';
                                    }elseif($param_code=='PF_STATUS_FORMATION'){
                                    $label='Status : ';
                                    }elseif($param_code=='PF_STATE_FORMATION'){
                                    $label='Etat : ';
                                    }
                                    @endphp

                                    <a href="#"
                                        class="text-muted text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                        <i class="flaticon-add-circular-button"></i> {{ $label }} {{ $name }}</a>

                                    @endforeach


                                </div>
                                <!--end::Contacts-->
                            </div>
                            <div class="my-lg-0 my-1">
                                <a href="/formations" class="btn btn-sm btn-icon btn-light-primary mr-3"
                                    data-toggle="tooltip" title="Liste des produits de formation"><i
                                        class="flaticon-list-2"></i></a>
                                        <button onclick="_formFormation({{ $formation->id }},0)"
                                            class="btn btn-sm btn-icon btn-light-primary mr-3" data-toggle="tooltip"
                                            title="Editer"><i class="flaticon-edit"></i></button>
                                        <input type="hidden" value="0" id="INPUT_HIDDEN_EDIT_MODE">
                            </div>
                        </div>
                        <!--end: Title-->
                        <!--begin: Content-->
                        <div class="d-flex align-items-center flex-wrap justify-content-between">
                            <div class="flex-grow-1 font-weight-bold text-dark-50 py-5 py-lg-2 mr-5">
                                <p>Disponibilité : {{ $formation->max_availability }}</p>
                                <p>Nombre de jours : {{ $formation->nb_days }}</p>
                                <p>Nombre d'heures : {{ $formation->nb_hours }}</p>
                                <p class="text-muted">{{ $formation->categorie->name }}</p>
                                <p>Description : {{ $formation->description }}</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center py-2">
                                <div class="d-flex align-items-center mr-10">
                                    <div class="mr-6">
                                        <div class="font-weight-bold mb-2">Crée le</div>
                                        <span
                                            class="btn btn-sm btn-text btn-light-primary text-uppercase font-weight-bold">{{ $createdAt }}</span>
                                    </div>
                                    <div class="mr-6">
                                        <div class="font-weight-bold mb-2">Modifiée le</div>
                                        <span
                                            class="btn btn-sm btn-text btn-light-warning text-uppercase font-weight-bold">{{ $updatedAt }}</span>
                                    </div>
                                    @if($deletedAt)
                                    <div class="">
                                        <div class="font-weight-bold mb-2">Archivée le</div>
                                        <span
                                            class="btn btn-sm btn-text btn-light-danger text-uppercase font-weight-bold">{{ $deletedAt }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!--end: Content-->
                    </div>
                    <!--end: Info-->
                </div>

                <!-- <div class="separator separator-solid my-7"></div>

                <div class="d-flex align-items-center flex-wrap">

                    <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-piggy-bank icon-2x text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column text-dark-75">
                            <span class="font-weight-bolder font-size-sm">Action de formations</span>
                            <span class="font-weight-bolder font-size-h5">0</span>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-confetti icon-2x text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column text-dark-75">
                            <span class="font-weight-bolder font-size-sm">Intervenants</span>
                            <span class="font-weight-bolder font-size-h5">10</span>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column text-dark-75">
                            <span class="font-weight-bolder font-size-sm">Ressources</span>
                            <span class="font-weight-bolder font-size-h5">8</span>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-file-2 icon-2x text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column flex-lg-fill">
                            <span class="text-dark-75 font-weight-bolder font-size-sm">50 Documents</span>
                            <a href="#" class="text-primary font-weight-bolder">View</a>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-chat-1 icon-2x text-muted font-weight-bold"></i>
                        </span>
                        <div class="d-flex flex-column">
                            <span class="text-dark-75 font-weight-bolder font-size-sm">50 Historiques</span>
                            <a href="#" class="text-primary font-weight-bolder">View</a>
                        </div>
                    </div>


                    <div class="d-flex align-items-center flex-lg-fill my-1">
                        <span class="mr-4">
                            <i class="flaticon-network icon-2x text-muted font-weight-bold"></i>
                        </span>
                    </div>

                </div> -->


            </div>
        </div>
        <!-- end::card infos -->

        <!--begin::Formation menu-->
        <div class="row">
            <!--begin::Aside-->
            <div class="col-lg-3">
                <!--begin::Profile Card-->
                <div class="card card-custom card-stretch">
                    <!--begin::Body-->
                    <div class="card-body pt-4">
                        <!--begin::Nav-->
                        <div class="navi navi-bold navi-hover navi-active navi-link-rounded">
                            <!-- <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV1" onclick="_loadContent('overview')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon-pie-chart-1"></i>
                                    </span>
                                    <span class="navi-text font-size-lg">Aperçu</span>
                                </a>
                            </div> -->
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV2" onclick="_loadContent('ficheTechnique')"
                                    class="navi-link css-af py-4 active">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon-star"></i>
                                    </span>
                                    <span class="navi-text font-size-lg">Fiche technique</span>
                                </a>
                            </div>
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV3" onclick="_loadContent('ficheExploitation')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon-analytics"></i>
                                    </span>
                                    <span class="navi-text font-size-lg">Fiche Exploitation</span>
                                    <!-- <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold">0</span>
                                    </span> -->
                                </a>
                            </div>
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV4" onclick="_loadContent('versions')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-folder-1"></i>
                                        </span>
                                    </span>
                                    <span class="navi-text font-size-lg">Versions</span>
                                    <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold" id="PF_NB_VERSIONS">0</span>
                                    </span>
                                </a>
                            </div>
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV5" onclick="_loadContent('catalogue')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="fa fas fa-list-ol"></i>
                                        </span>
                                    </span>
                                    <span class="navi-text font-size-lg">Catalogue</span>
                                </a>
                            </div>
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV6" onclick="_loadContent('tarification')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-price-tag"></i>
                                        </span>
                                    </span>
                                    <span class="navi-text font-size-lg">Tarification</span>
                                </a>
                            </div>
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV7" onclick="_loadContent('historique')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon2-time"></i>
                                        </span>
                                    </span>
                                    <span class="navi-text font-size-lg">Historique</span>
                                </a>
                            </div>
                            <!-- Structure temporelle -->
                            @if($type_pf=='PF_TYPE_DIP')
                            <div class="navi-item mb-2">
                                <a style="cursor:pointer;" id="NAV8" onclick="_loadContent('structure')"
                                    class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-clock-1"></i>
                                        </span>
                                    </span>
                                    <span class="navi-text font-size-lg">Structure H & T</span>
                                </a>
                            </div>
                            @endif
                            <!-- Structure temporelle -->

                        </div>
                        <!--end::Nav-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Profile Card-->
            </div>
            <!--end::Aside-->
            <!--begin::Content-->
            <div class="col-lg-9" id="BLOCK_CONTENT_NAVIGATION">

            </div>
            <!--end::Content-->
        </div>
        <!--end::Formation menu-->
    </div>
</div>

<x-modal id="modal_form_formation" content="modal_form_formation_content" />
<x-modal id="modal_sheet_formation" content="modal_sheet_formation_content" />

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script src="{{ asset('custom/plugins/jstree/dist/jstree.min.js') }}"></script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=1') }}"></script>
<script src="{{ asset('custom/js/view-formation.js?v=3') }}"></script>
<script type="text/javascript">
    //form
function _formFormation(id_formation,mode,type=0) {
    //mode == 0, ajout ou edit depuis la page view formation produit parent
    //mode == 1, ajout edit

    //type == 0,edit
    //type == 1, ajout
    $('#INPUT_HIDDEN_EDIT_MODE').val(mode);
    var modal_id = 'modal_form_formation';
    var modal_content_id = 'modal_form_formation_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/formation/' + id_formation + '/' + type,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
</script>
@endsection
