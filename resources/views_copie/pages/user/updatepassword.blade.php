{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

    @php
        $createdAt = $updatedAt = $deletedAt = $started_at= $ended_at = '';
        if($row){
        $createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
        $updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
        $deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
        if($row->started_at!=null){
        $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->started_at);
        $started_at = $dt->format('d/m/Y');
        }
        if($row->ended_at!=null){
        $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->ended_at);
        $ended_at = $dt->format('d/m/Y');
        }
        }
    @endphp

    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!-- begin::hidden input helper -->
        <input type="hidden" id="VIEW_INPUT_AF_ID_HELPER" value="{{ $row->id }}"/>
        <input type="hidden" id="INPUT_HIDDEN_PARAMETERS" value="{{ $show_proposal }}"/>
        <!-- end::hidden input helper -->
        <!--begin::Container-->
        <div class="container-fluid">
            <div id="BLOCK_UKNOWN_DATE" class="d-none">
                <div class="alert alert-custom alert-outline-warning fade show gutter-b" role="alert">
                    <div class="alert-icon">
                        <i class="flaticon-warning"></i>
                    </div>
                    <div class="alert-text">Pas de dates connues actuellement</div>
                </div>
            </div>
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
                                    <p class="text-muted mb-0">{{ $row->code }}</p>
                                    <a href="#"
                                       class="d-flex align-items-center text-dark text-hover-primary font-size-h5 font-weight-bold mr-3">{{ $row->title }}
                                        <i class="flaticon2-{{ ($row->is_active===1)?'correct':'cross' }} text-{{ ($row->is_active===1)?'success':'danger' }} icon-md ml-2"></i></a>
                                    <!--end::Name-->
                                    <!--begin::Contacts-->
                                    <div class="d-flex flex-wrap my-2">
                                        <a href="#"
                                           class="text-muted text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                            <i class="flaticon-add-circular-button"></i> Type dispositif :
                                            {{ $row->device_type }}</a>
                                        <a href="#"
                                           class="text-muted text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                            <i class="flaticon-time"></i> Durée {{ $row->nb_days }} jours /
                                            {{ $row->nb_hours }} heures</a>
                                        <a href="#"
                                           class="text-muted text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                            <i class="flaticon-users"></i> Nb stagiaires max :
                                            {{ $row->max_nb_trainees }}</a>
                                    </div>
                                    <!--end::Contacts-->
                                </div>
                                <div class="my-lg-0 my-1">
                                    <a href="/afs" class="btn btn-sm btn-icon btn-light-primary mr-1"
                                       data-toggle="tooltip"
                                       title="Liste des AFs"><i class="flaticon-list-2"></i></a>
                                    <button onclick="_formAf({{ $row->id }})"
                                            class="btn btn-sm btn-icon btn-light-primary mr-1" data-toggle="tooltip"
                                            title="Editer"><i class="flaticon-edit"></i></button>

                                    <a target="_blank" href="/pdf/af/technical/sheet/{{ $row->id }}/1" class="btn btn-sm btn-icon btn-light-primary mr-1" data-toggle="tooltip" title="Visualiser la fiche technique"><i class="far fa-file-pdf"></i></a>
                                    <a target="_blank" href="/pdf/af/technical/sheet/{{ $row->id }}/2" class="btn btn-sm btn-icon btn-light-primary mr-1" data-toggle="tooltip" title="Télécharger la fiche technique"><i class="flaticon-download"></i></a>
                                </div>
                            </div>
                            <!--end: Title-->
                            <!--begin: Content-->
                            <div class="d-flex align-items-center flex-wrap justify-content-between">
                                <div class="flex-grow-1 font-weight-bold text-dark-50 py-5 py-lg-2 mr-5">
                                    <p>
                                    <span
                                        class="label label-md label-outline-success label-pill label-inline mr-2">{{ $state }}</span>
                                        <span
                                            class="label label-md label-outline-danger label-pill label-inline">{{ $status }}</span>
                                    </p>
                                    <p class="text-muted">{{ $row->formation->categorie->name }}</p>
                                    <p>Produit : <span class="text-info">{{ $row->formation->title }}</span></p>
                                    <p>BPF F3 : <span class="text-info">{{ $bpf_main_objective }}</span> - BPF F4 :
                                        <span class="text-info">{{ $bpf_training_specialty }}</span></p>
                                    <p>Lieu de formation : <span
                                            class="text-info">{{ $training_site }} {{ $row->other_training_site }}</span>
                                    </p>
                                    
                                    <p class="text-primary">Feuille de présence et absences : 
                                        <a class="mr-2" target="_blank" href="/pdf/attendance-absence-sheet/{{ $row->id }}/1/0"><i class="far fa-file-pdf text-primary"></i></a>
                                        <a class="mr-2" target="_blank" href="/pdf/attendance-absence-sheet/{{ $row->id }}/2/0"><i class="flaticon-download text-primary"></i></a>
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap align-items-center py-2">
                                    <div class="d-flex align-items-center mr-10">
                                        @if($started_at)
                                            <div class="mr-6">
                                                <div class="font-weight-bold mb-2">Début le</div>
                                                <span
                                                    class="btn btn-sm btn-text btn-light-info text-uppercase font-weight-bold">{{ $started_at }}</span>
                                            </div>
                                        @endif
                                        @if($ended_at)
                                            <div class="mr-6">
                                                <div class="font-weight-bold mb-2">Fin le</div>
                                                <span
                                                    class="btn btn-sm btn-text btn-light-info text-uppercase font-weight-bold">{{ $ended_at }}</span>
                                            </div>
                                        @endif
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
                    <div class="separator separator-solid my-7"></div>
                    <!--begin: Items-->
                    <div class="d-flex align-items-center flex-wrap">
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-piggy-bank icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Session</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_sessions">0</span>
                            </div>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-confetti icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Inscriptions</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_enrollments_stagiaires">0</span>
                            </div>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Intervenants</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_enrollments_intervenants">0</span>
                            </div>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Devis</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_devis">0</span>
                            </div>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Conventions</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_conventions">0</span>
                            </div>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="d-flex align-items-center flex-lg-fill mr-5 my-1">
                        <span class="mr-4">
                            <i class="flaticon-pie-chart icon-2x text-muted font-weight-bold"></i>
                        </span>
                            <div class="d-flex flex-column text-dark-75">
                                <span class="font-weight-bolder font-size-sm">Contrats</span>
                                <span class="font-weight-bolder font-size-h5" id="nb_contrats">0</span>
                            </div>
                        </div>
                        <!--end: Item-->

                    </div>
                    <!--begin: Items-->
                </div>
            </div>
            <!-- end::card infos -->

            <!--begin::Profile Change Password-->
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
                                    <a style="cursor:pointer;" id="NAV2" onclick="_loadContent('sheets')"
                                       class="navi-link css-af py-4 active">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon2-accept"></i>
                                    </span>
                                        <span class="navi-text font-size-lg">Fiche technique</span>
                                    </a>
                                </div>
                                @if(auth()->user()->roles[0]->code!='FORMATEUR')    
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV11" onclick="_loadContent('tarification')"
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
                                    <a style="cursor:pointer;" id="NAV9" onclick="_loadContent('sessions')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-event-calendar-symbol"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Sessions</span>
                                        <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold"
                                              id="nb_sessions_1">0</span>
                                    </span>
                                    </a>
                                </div>
                                <!-- Périodes des stages -->
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV13" onclick="_loadContent('stages')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-event-calendar-symbol"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Périodes de stage</span>
                                        <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold"
                                              id="nb_stage_periods">0</span>
                                    </span>
                                    </a>
                                </div>
                                <!-- Périodes des stages -->
                                <!-- Propositions de stages -->
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV14" onclick="_loadContent('proposals')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-event-calendar-symbol"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Propositions de stage</span>
                                        <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold"
                                              id="nb_stage_proposals">0</span>
                                    </span>
                                    </a>
                                </div>
                                <!-- Propositions de stages -->
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV4" onclick="_loadContent('dates')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-event-calendar-symbol"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Dates & séances</span>
                                    </a>
                                </div>
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV6" onclick="_loadContent('ressources')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon2-list-1"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Ressources</span>
                                    </a>
                                </div>
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV5" onclick="_loadContent('intervenants')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon-users-1"></i>
                                    </span>
                                        <span class="navi-text font-size-lg">Intervenants</span>
                                        <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold"
                                              id="nb_enrollments_intervenants_1">0</span>
                                    </span>
                                    </a>
                                </div>
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV3" onclick="_loadContent('inscriptions')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon2-group"></i>
                                    </span>
                                        <span class="navi-text font-size-lg">Inscriptions</span>
                                        <span class="navi-label">
                                        <span class="label label-light-primary label-rounded font-weight-bold"
                                              id="nb_enrollments_stagiaires_1">0</span>
                                    </span>
                                    </a>
                                </div>
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV12" onclick="_loadContent('groups')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <i class="flaticon2-group"></i>
                                    </span>
                                        <span class="navi-text font-size-lg">Groupes & groupements</span>
                                    </a>
                                </div>
                                @endif
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV10" onclick="_loadContent('schedulecontacts')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon-event-calendar-symbol"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Planification</span>
                                    </a>
                                </div>
                                @if(auth()->user()->roles[0]->code!='FORMATEUR')    
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV15" onclick="_loadContent('certifications')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon2-correct"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Certifications</span>
                                    </a>
                                </div>
                                @endif
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV7" onclick="_loadContent('documents')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon2-files-and-folders"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Documents AF</span>
                                        <!-- <span class="navi-label">
                                            <span class="label label-light-danger label-rounded font-weight-bold">5</span>
                                        </span> -->
                                    </a>
                                </div>
                                @if(auth()->user()->roles[0]->code!='FORMATEUR')    
                                <div class="navi-item mb-2">
                                    <a style="cursor:pointer;" id="NAV8" onclick="_loadContent('historique')"
                                       class="navi-link css-af py-4">
                                    <span class="navi-icon mr-2">
                                        <span class="svg-icon">
                                            <i class="flaticon2-time"></i>
                                        </span>
                                    </span>
                                        <span class="navi-text font-size-lg">Historique</span>
                                    </a>
                                </div>
                                @endif
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
            <!--end::Profile Change Password-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
    <x-modal id="modal_form_af" content="modal_form_af_content"/>
    <x-modal id="modal_form_session" content="modal_form_session_content"/>
    <x-modal id="modal_form_committee" content="modal_form_committee_content"/>
    <x-modal id="modal_form_enrollment" content="modal_form_enrollment_content"/>
    <x-modal id="modal_form_schedulecontacts" content="modal_form_schedulecontacts_content"/>
    <x-modal id="modal_form_enrollment_intervenants" content="modal_form_enrollment_intervenants_content"/>
    <x-modal id="modal_form_remuneration" content="modal_form_remuneration_content"/>
    <x-modal id="modal_form_contract" content="modal_form_contract_content"/>
    <x-modal id="modal_sheet_formation" content="modal_sheet_formation_content"/>
    <x-modal id="modal_form_sessiondate" content="modal_form_sessiondate_content"/>
    <x-modal id="modal_form_estimate" content="modal_form_estimate_content"/>
    <x-modal id="modal_form_estimateItem" content="modal_form_estimateItem_content"/>
    <!-- conventions / contrats -->
    <x-modal id="modal_form_convocation" content="modal_form_convocation_content"/>
    <x-modal id="modal_form_agreement" content="modal_form_agreement_content"/>
    <x-modal id="modal_form_agreementItem" content="modal_form_agreementItem_content"/>
    <x-modal id="modal_form_funding" content="modal_form_funding_content"/>
    <x-modal id="modal_form_fundingpayment" content="modal_form_fundingpayment_content"/>
    <!--Factures-->
    <x-modal id="modal_form_invoice" content="modal_form_invoice_content" />
    <x-modal id="modal_schedule_details" content="modal_schedule_details_content" />
    <x-modal id="modal_form_pointage" content="modal_form_pointage_content" />
    <x-modal id="modal_form_score" content="modal_form_score_content" />
    <x-modal id="modal_form_students_invoices" content="modal_form_students_invoices_content" />
    <x-modal id="modal_form_refund" content="modal_form_refund_content" />
    <x-modal id="modal_form_attached_documents" content="modal_form_attached_documents_content" />
    <x-modal id="modal_form_invoice_from_agreement" content="modal_form_invoice_from_agreement_content" />
    <x-modal id="modal_form_mail" content="modal_form_mail_content" />
    <x-modal id="modal_form_payment" content="modal_form_payment_content" />
    <x-modal id="modal_form_cert_sessions" content="modal_form_cert_sessions_content" />
@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" integrity="sha512-nNlU0WK2QfKsuEmdcTwkeh+lhGs6uyOxuUs+n+0oXSYDok5qy0EI0lt01ZynHq6+p/tbgpZ7P+yUb+r71wqdXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=4') }}"></script>
    <!-- <script src="{{ asset('custom/js/view-af.js?v=1') }}"></script> -->
    <script src="{{ asset('custom/js/af-form-estimate.js?v=1') }}"></script>

    <script>
        /* $(document).ready(function() {
            $('.select2').select2();
        }); */
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
        var _loadContent = function (viewtype) {
            var block_id = 'BLOCK_CONTENT_NAVIGATION';
            var row_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            KTApp.block('#' + block_id, {
                overlayColor: '#000000',
                state: 'danger',
                message: 'Please wait...'
            });
            $.ajax({
                url: '/view/content/construct/af/' + viewtype + '/' + row_id,
                type: 'GET',
                dataType: 'html',
                success: function (html, status) {
                    $('#' + block_id).html(html);
                },
                error: function (result, status, error) {

                },
                complete: function (result, status) {
                    KTApp.unblock('#' + block_id);
                }
            });
            $(".css-af").each(function () {
                $(this).removeClass("active");
            });
            btn_id = 'NAV1';
            if (viewtype == 'sheets') {
                btn_id = 'NAV2';
            } else if (viewtype == 'inscriptions') {
                btn_id = 'NAV3';
            } else if (viewtype == 'dates') {
                btn_id = 'NAV4';
            } else if (viewtype == 'intervenants') {
                btn_id = 'NAV5';
            } else if (viewtype == 'sessions') {
                btn_id = 'NAV9';
            } else if (viewtype == 'schedulecontacts') {
                btn_id = 'NAV10';
            } else if (viewtype == 'certifications') {
                btn_id = 'NAV15';
            } else if (viewtype == 'tarification') {
                btn_id = 'NAV11';
            } else if (viewtype == 'ressources') {
                btn_id = 'NAV6';
            } else if (viewtype == 'documents') {
                btn_id = 'NAV7';
            } else if (viewtype == 'historique') {
                btn_id = 'NAV8';
            } else if (viewtype == 'groups') {
                btn_id = 'NAV12';
            } else if (viewtype == 'stages') {
                btn_id = 'NAV13';
            } else if (viewtype == 'proposals') {
                btn_id = 'NAV14';
            }
            $('#' + btn_id).addClass("active");
        }

        var parameter = $('#INPUT_HIDDEN_PARAMETERS').val();
        if(parameter=="p"){
            _loadContent('proposals');
        }else{
           _loadContent('sheets'); 
        }
        var _formAf = function (af_id) {
            var modal_id = 'modal_form_af';
            var modal_content_id = 'modal_form_af_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/af/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function (html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function (result, status, error) {

                },
                complete: function (result, status) {

                }
            });
        }
        _updateAfUknownDate();

        function _updateAfUknownDate() {
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            $.ajax({
                url: '/api/check/af/has-unknown-date/' + af_id,
                type: 'GET',
                dataType: 'JSON',
                success: function (result, status) {
                    //console.log(result.is_unknown_date);
                    if (result.is_unknown_date) {
                        $('#BLOCK_UKNOWN_DATE').removeClass('d-none');
                    } else {
                        $('#BLOCK_UKNOWN_DATE').addClass('d-none');
                    }
                },
                error: function (result, status, error) {
                },
                complete: function (result, status) {
                }
            })
        }

        _update_af_stats();

        function _update_af_stats() {
            var spinner = '<div class="spinner spinner-primary spinner-sm"></div>';
            $('#nb_sessions,#nb_sessions_1,#nb_enrollments_stagiaires,#nb_enrollments_stagiaires_1,#nb_enrollments_intervenants,#nb_enrollments_intervenants_1,#nb_devis,#nb_conventions,#nb_contrats,#nb_stage_periods').html(spinner);
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            if (af_id > 0) {
                $.ajax({
                    url: '/api/statistics/af/' + af_id,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (result, status) {
                        $('#nb_sessions,#nb_sessions_1').html(result.nb_sessions);
                        $('#nb_enrollments_stagiaires,#nb_enrollments_stagiaires_1').html(result.nb_enrollments_stagiaires);
                        $('#nb_enrollments_intervenants,#nb_enrollments_intervenants_1').html(result.nb_enrollments_intervenants);
                        $('#nb_devis').html(result.nb_devis);
                        $('#nb_conventions').html(result.nb_conventions);
                        $('#nb_contrats').html(result.nb_contrats);
                        $('#nb_stage_periods').html(result.nb_stage_periods);
                    },
                    error: function (result, status) {
                    },
                    complete: function (result, status) {
                    }
                });
            }
        }

    </script>
@endsection
