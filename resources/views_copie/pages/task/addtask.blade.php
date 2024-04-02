{{-- Extends layout --}}
@extends('layout.default')


{{-- Styles Section --}}
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .CodeMirror-scroll {
            height: 100px;
        }

        .select2-container {
            width: 100% !important;
        }

        /*previous button*/

        #back {
            text-decoration: none;
            color: #3F4254;
            background: white;
            padding: 5px 46px;
            border-radius: 4px;
            font-size: 23px;
        }

        #back:hover {
            background: #3F425478;
        }

        #back:focus {
            outline: 3px solid rgb(220 53 69 / 50%);

        }

        #backdiv {
            justify-content: space-between;
        }
    </style>
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>

@endsection


{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div id="backdiv" class="mb-3">
        <a id="back" onclick="getreturn()" title="Ramener l'écran précédant">&#8249;&#8249;</a>
    </div>
    <div class="card card-custom card-body">
        <div class="card-toolbar">
            <ul class="nav nav-tabs nav-bold nav-tabs-line nav-tabs-line-3x">
                <!--end::Item-->
                <!--begin::Item-->
                <li class="nav-item mr-3">
                    <a class="nav-link active" data-toggle="tab" href="#kt_user_edit_tab_3">
                        <span class="nav-icon">
                            <span class="svg-icon">
                                <!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Communication/Shield-user.svg-->
                                <img style="width: 27px;"
                                    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAADB0lEQVRoge2azUtUURTAf2eUGZpxkYZQQ8s2IS6bNCv6cBcIhbVTCNIxg2hVGpRQ/0GLYhKhsk1GC0Mwwj7AyDTaJO2Gdir04bTQ8CPfaTEOMwzzdN7c98ZB57canvede37v3PPm+t7ANkHcCNLwUFvWlAcCYSfnKcxWCJc/dcpL0xx8pgEACpEAEAhbyn03crCtSN2Q+kMJ7gJtwL58gk1FxVGFIzHVfMYpzIowuLib298uyEquMZV2J4f+cAe47iQxrxAIo9yoSqBAb64xtiKqtAmgQtPnTvm40UT5Xlk7Nqvk4ZgeVRi3oB0bEdseSa35zSSKwWRUPkA6p1zYViQb06teaOx8+86Vu1YpkHdFUuS6QodiOiMQLrBqM1NR2Z990GksVyriU7qBmQJOnfEJ3W7k4LgiuZjskmFg2I1YhbJteqQsUmqURUoNV+5aTjhwTwM1AXqBK4AoDCaW6YlflWWTuEUVWZd4AZxJHRO4tifAUtxmM5gvRVtaWRKLmX9TuGQavygiWRK/xMcRVW5lDLFM5/BcJIfE6ckO+VopDJOuzCPTeTwVqRtSf3WAIZISP1MSDf16cA1eAyHgVdBPn+lcnonUDak/mOC5QAtJieaUhGXxFthLUuLs+4uyZDqfJyLFlgAPRLZCAlwWyZaoIN0TXkqAiyK5JCaiMp0pITDqhQS4JJKvxC4/57yQAJe2KKEET8noiYkOmW6Maf2axRugFmXk9wqt8ajZfmojjCvSNKBhoBX4m+qJxpjWr5GWmF+h1XRTuBnGIqurNJN8hvwutZzWv+xqBUaDAc57LQFuLC2hGUCFUCSmI5bFSSBYjOWUiRs9cgpAlBOpAwLPFmpoj9s8OfcCcxHluwoiwpjA2D9l7EtU5lzIzRHGIlNdctyNREzZNv+zl0VKjbJIqeH4ruXlmysTdl5FnL5DLza2FVGYheSr4eKlk5tIvx5b/zhvN8a2Ij54otCjMB6JbXFbpB/fDdgNsRVZqKavKgEWtBfyOxOX+QE8Ds5xc4vzKLPz+A8LnzkXUbeiTgAAAABJRU5ErkJggg==">
                                <!--end::Svg Icon-->
                            </span>
                        </span>
                        <span class="nav-text font-size-lg" style="margin-left: 10px;">Créer une tâche</span>
                    </a>
                </li>
                <!--end::Item-->
            </ul>
        </div>
        <form class="form" id="addtask">
            @method('post')
            @csrf
            <div class="tab-content">
                <div class="tab-pane active" id="kt_user_edit_tab_3" role="tabpanel">
                    <!--begin::Body-->
                    <div class="card-body">
                        <!--begin::Row-->
                        <div class="row">
                            {{-- <div class="col-xl-2"></div> --}}
                            <div class="col-xl-6">
                                <div class="row mb-5">
                                    <label class="col-3"></label>
                                    <div class="col-9" id="popup" style="display: none;">
                                        <div class="alert alert-custom alert-light-danger fade show py-4" role="alert">
                                            <div class="alert-icon">
                                                <i class="flaticon-warning"></i>
                                            </div>
                                            <div class="alert-text font-weight-bold">Merci de bien vouloir vérifier vos
                                                données.</div>
                                            <div class="alert-close">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">
                                                        <i class="la la-close"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-9" id="popup1" style="display: none;">
                                        <div class="alert alert-custom alert-light-danger fade show py-4" role="alert"
                                            style="background-color: #B3E3CD;">
                                            <div class="alert-text font-weight-bold" style="color: #14603C;">Votre tâche a
                                                été créée avec avec succès!</div>
                                            <div class="alert-close">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close" style="color: #14603C !important;">
                                                    <span aria-hidden="true">
                                                        <i class="la la-close"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Row-->
                                <!--begin::Row-->
                                <div class="row">
                                    <label class="col-4"></label>
                                    <div class="col-9">
                                        <!-- <h6 class="text-dark font-weight-bold mb-10">Modifier ou récupérer votre mot de passe :</h6> -->
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-3"><b>Motivation:</b></label>
                                    <div class="col-9">
                                        <div class="col-8 checkbox-inline">
                                            <label class="checkbox">
                                                <input name="motivation" value="intern" type="radio" checked>
                                                <span></span>Tâche à réaliser en interne</label>
                                            <label class="checkbox">
                                                <input name="motivation" value="contact" type="radio">
                                                <span></span>Tâche concernant un client/prospect/étudiant</label>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Group-->
                            </div>
                            <!--end::Row-->
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-8 contact-card" style="display: none;">
                                    <h5 class="card-header h6">Contact</h5>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Entité</label>
                                            <div class="col-5">
                                                <select id="entity" name="entity" class="entity-deep-search">
                                                    <option value="">Sélectionner l'entité</option>
                                                </select>
                                            </div>
                                            <div class="col-1">
                                                <button id="entity_search" class="btn btn-success">
                                                    <i class="fa fa-search-plus"></i>
                                                </button>
                                            </div>
                                            <div class="col-3">
                                                <button id="entity_add" class="btn btn-primary btn-block">Créer une
                                                    entité</button>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Contact</label>
                                            <div class="col-5">
                                                <select id="contact" name="contact">
                                                    <option selected value="">Sélectionner le contact</option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <button id="contact_add" class="btn btn-primary btn-block">Créer un
                                                    contact</button>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Adresse</label>
                                            <div class="col-9">
                                                <textarea rows="3" class="form-control form-control-solid" name="contact_address" id="contact_address"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">CP</label>
                                            <div class="col-9">
                                                <input class="form-control form-control-lg form-control-solid"
                                                    type="text" id="contact_cp" name="contact_cp">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Ville</label>
                                            <div class="col-9">
                                                <input class="form-control form-control-lg form-control-solid"
                                                    type="text" id="contact_city" name="contact_city">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Téléphone</label>
                                            <div class="col-9">
                                                <input class="form-control form-control-lg form-control-solid"
                                                    type="text" id="contact_phone" name="contact_phone">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Mail</label>
                                            <div class="col-9">
                                                <input class="form-control form-control-lg form-control-solid"
                                                    type="text" id="contact_email" name="contact_email">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-8">
                                    <h5 class="card-header h6">Résumé</h5>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Résumé<span
                                                    aria-hidden="true"
                                                    style="padding-left:2px; color:#DE350B;">*</span></label>
                                            <div class="col-9">
                                                <input class="form-control form-control-lg form-control-solid"
                                                    type="text" id="resume" name="resume" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Type<span
                                                    aria-hidden="true"
                                                    style="padding-left:2px; color:#DE350B;">*</span></label>
                                            <div class="col-9">
                                                <select id="type" name="type"
                                                    class="form-control form-control-lg form-control-solid" required>
                                                    <option selected value="">Sélectionner le type</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Source</label>
                                            <div class="col-9">
                                                <select id="source" name="source"
                                                    class="form-control form-control-lg form-control-solid">
                                                    <option selected value="">Sélectionner la source</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Object
                                                concerné</label>
                                            <div class="col-8 checkbox-inline">
                                                <label class="checkbox">
                                                    <input name="task_object" value="glblaf" type="radio"
                                                        onchange="validateObject('glblaf')">
                                                    <span></span>AF</label>
                                                <label class="checkbox">
                                                    <input name="task_object" value="glblpf" type="radio"
                                                        onchange="validateObject('glblpf')">
                                                    <span></span>PF</label>
                                                <label class="checkbox">
                                                    <input name="task_object" value="" type="radio"
                                                        onchange="validateObject('')" checked>
                                                    <span></span>Demande Générale</label>
                                                {{-- <label class="checkbox">
                                                    <input type="checkbox" onclick="validateEntite(this)">
                                                    <span></span>Entité</label>
                                                <label class="checkbox">
                                                    <input type="checkbox" onclick="validateContact(this)">
                                                    <span></span>Contact</label> --}}
                                            </div>
                                        </div>
                                        <div class="form-group row glblaf">
                                            <label class="col-form-label col-3 text-lg-right text-left">Action de
                                                formation</label>
                                            <div class="col-9">
                                                <select id="aflist" name="aflist">
                                                    <option selected value="">Sélectionner l'AF</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row glblpf">
                                            <label class="col-form-label col-3 text-lg-right text-left">Produit de
                                                formation</label>
                                            <div class="col-9">
                                                <select id="pflist" name="pflist">
                                                    <option selected value="">Sélectionner le PF</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-8">
                                    <h5 class="card-header h6">Commentaires</h5>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label
                                                class="col-form-label col-3 text-lg-right text-left">Commentaires</label>
                                            <div class="col-9">
                                                <textarea class="form-control form-control-solid" name="notes" id="notes" name="notes"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-8">
                                    <h5 class="card-header h6">Description</h5>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Description</label>
                                            <div class="col-9">
                                                <textarea id="myTextarea" name="myTextarea"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Superviseur<span
                                                    aria-hidden="true"
                                                    style="padding-left:2px; color:#DE350B;">*</span></label>
                                            <div class="col-9">
                                                <!-- <input class="form-control form-control-lg form-control-solid mb-1" type="text" id="rapporteur" value="{{ auth()->user()->name }} {{ auth()->user()->lastname }}"> -->
                                                <select id="rapporteur" name="rapporteur"
                                                    class="form-control form-control-lg form-control-solid" required>
                                                    <option value="">Sélectionner le supervisseur</option>
                                                    <!-- <option selected value="0">{{ auth()->user()->name }} {{ auth()->user()->lastname }}</option>                                                         -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Responsable<span
                                                    aria-hidden="true"
                                                    style="padding-left:2px; color:#DE350B;">*</span></label>
                                            <div class="col-9">
                                                <select id="responsable" name="responsable"
                                                    class="form-control form-control-lg form-control-solid" required>
                                                    <option selected value="">Sélectionner le responsable</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">État</label>
                                            <div class="col-9">
                                                <select id="etat" name="etat"
                                                    class="form-control form-control-lg form-control-solid">
                                                    <option selected value="">Sélectionner l'état</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Date de
                                                début</label>
                                            <div class="col-9">
                                                <div class="position-relative d-flex align-items-center"
                                                    style="align-items: center !important;">
                                                    <input class="form-control form-control-solid ps-12 flatpickr-input"
                                                        id="datedebut" name="datedebut"
                                                        placeholder="         --- Sélectionner une date de début ---"
                                                        name="due_date" type="text" readonly="readonly"
                                                        style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Date
                                                d'échéance</label>
                                            <div class="col-9">
                                                <div class="position-relative d-flex align-items-center"
                                                    style="align-items: center !important;">
                                                    <input class="form-control form-control-solid ps-12 flatpickr-input"
                                                        id="dateecheance" name="dateecheance"
                                                        placeholder="         --- Sélectionner une date d'échéance ---"
                                                        name="due_date" type="text" readonly="readonly"
                                                        style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Date de
                                                rappel</label>
                                            <div class="col-9">
                                                <div class="position-relative d-flex align-items-center"
                                                    style="align-items: center !important;">
                                                    <input class="form-control form-control-solid ps-12 flatpickr-input"
                                                        id="daterappel" name="daterappel"
                                                        placeholder="         --- Sélectionner une date d'échéance ---"
                                                        name="due_date" type="text"
                                                        style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Mode de
                                                rappel</label>
                                            <div class="col-8 checkbox-inline p-3">
                                                <label class="checkbox">
                                                    <input type="radio" name="rappelmode" class="rappelmode"
                                                        id="solaris" value="solaris" checked>
                                                    <span></span>Solaris</label>
                                                <label class="checkbox">
                                                    <input type="radio" name="rappelmode" class="rappelmode"
                                                        id="email" value="email">
                                                    <span></span>Email</label>
                                                <label class="checkbox">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Mode de
                                                réponse</label>
                                            <div class="col-9">
                                                <select id="reponsemode" name="reponsemode"
                                                    class="form-control form-control-lg form-control-solid">
                                                    <option selected value="">Sélectionner le mode de réponse
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Priorité</label>
                                            <div class="col-9">
                                                <select id="priorite" name="priorite"
                                                    class="form-control form-control-lg form-control-solid">
                                                    <option value="">Sélectionner la priorité</option>
                                                    <option value="plus haute">Plus haute</option>
                                                    <option value="haute">haute</option>
                                                    <option value="bas">Bas</option>
                                                    <option value="plus bas">Plus bas</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-3 text-lg-right text-left">Envoyer un mail de création</label>
                                            <div class="col-8 checkbox-inline p-3">
                                                <label class="checkbox">
                                                    <input type="checkbox" name="send_mail"
                                                        id="send_mail" value="1" checked>
                                                    <span></span></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                        <!--begin::Footer-->
                        <div class="card-footer pb-0">
                            <div class="row">
                                <div class="col-xl-2"></div>
                                <div class="col-xl-7">
                                    <div class="d-flex flex-center">
                                        <div data-onloading="false" class="w-100">
                                            <button type="submit" data-loading-text="Création en cours..."
                                                class="btn btn-block btn-light-primary font-weight-bold"
                                                id="BTN_SAVE">Créer</button>
                                            <input type="reset" onclick="reset_form()"
                                                class="btn btn-block btn-clean font-weight-bold" id="clear"
                                                value="Réinitialiser">
                                        </div>
                                        <div class="spinner-border text-primary" data-onloading="true"
                                            style="display: none;" role="status">
                                            <span class="visually-hidden"></span>
                                        </div>
                                        {{-- <a href="#" class="btn btn-clean font-weight-bold" id="clear"
                                                onclick="refresh()">Annuler</a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Footer-->
                    </div>
                    <!--end::Tab-->
                </div>
        </form>
        <x-modal id="modal_form_contact" content="modal_form_contact_content" />
        <x-modal id="modal_dt_entities" content="modal_dt_entities_content" dialogstyle="max-width: 90%;" />
        <x-modal id="modal_form_entitie" content="modal_form_entitie_content" />
        <x-modal id="modal_form_mail" content="modal_form_mail_content" />

    </div>
    <!--end::Card-->
@endsection

{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"
        crossorigin="anonymous"></script> --}}

    {{-- page scripts --}}
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
    <script src="{{ asset('custom/js/general.js?v=1') }}"></script>
    <script>
        $(".glblaf, .glblpf").hide();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $("select").select2();
        });

        $(function() {
            $('.flatpickr-input').datepicker({
                language: 'fr',
                format: 'dd/mm/yyyy',
                todayHighlight: true,
                orientation: "bottom left",
                templates: {
                    leftArrow: '<i class="la la-angle-left"></i>',
                    rightArrow: '<i class="la la-angle-right"></i>'
                },
                lang: 'fr'
            })
        });

        //textarea
        const easyMDE = new EasyMDE({
            element: document.getElementById('myTextarea')
        });

        function validateObject(value) {
            $(".glblaf, .glblpf").hide();
            if (value.length > 0) {
                $("." + value).show();
            }
        }


        function reset() {
            $(':input').val('');
            $("#rapporteur").select2().val("0").trigger("change");
            $("#responsable").select2().val("0").trigger("change");
            $("#aflist").select2().val("0").trigger("change");
            $("#pflist").select2().val("0").trigger("change");
            // $("#entite").select2().val("0").trigger("change");
            $("#contact").select2().val("0").trigger("change");
            $("#source").select2().val("0").trigger("change");
            $("#type").select2().val("0").trigger("change");
            $("#reponsemode").select2().val("0").trigger("change");
            $("#etat").select2().val("0").trigger("change");
        }

        function refresh() {
            window.location = '/';
        }

        _loadcontacts(['rapporteur', 'responsable'], true);
        // _loadcontacts(['contact']);
        _loadentities('entity');
        _loadafs('aflist');
        _loadpfs('pflist');
        _getSource('source');
        _getType('type');
        _getMode('reponsemode');
        _getEtat('etat');

        function _loadcontacts(select_ids, intern = false, entity_id = 0, default_contact = 0) {
            select_ids.map((select_id) => {
                $('#' + select_id + ' option[value!=""]').remove();
            });
            $.ajax({
                type: 'GET',
                url: '/api/select/options/getcontacts' + (entity_id > 0 ? '/' + entity_id : '') + (intern ?
                    '?intern=1' : ''),
                dataType: 'json',
                success: function(response) {
                    var array = response;
                    if (array != '') {
                        select_ids.map((select_id) => {
                            for (i in array) {
                                $('#' + select_id).append("<option value='" + array[i].id + "' " + (
                                        default_contact > 0 && array[i].id == default_contact ? 'selected' : '') + ">" +
                                    array[i].name +
                                    "</option>");
                                $('#' + select_id).select2();
                                if (default_contact > 0) {
                                    $('#' + select_id).trigger('change');
                                }
                            }
                        });
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _loadentities(select_id, selected_value = 0) {
            $('#' + select_id + ' option[value!=""]').remove();

            $.ajax({
                url: '/api/select/options/entities/type/A',
                dataType: 'json',
                success: function(result) {
                    if (result != '') {
                        for (i in result) {
                            $('#' + select_id).append("<option value='" + result[i].id + "'>" + result[i].name +
                                "</option>");
                        }
                        if (selected_value != 0 && selected_value != '') {
                            $('#' + select_id).select2().val(selected_value).trigger('change');
                        }
                    }
                }
            });
        }

        function _loadafs(select_id) {
            $.ajax({
                type: 'GET',
                url: '/api/sdt/listAfs',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            const af_name =
                                `${array[i].code} - ${array[i].title} - ${array[i].start_date} - ${array[i].pf_title}`;
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + af_name +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _loadpfs(select_id) {
            $.ajax({
                type: 'GET',
                url: '/api/sdt/listPfs',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].title +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getSource(select_id) {
            $.ajax({
                type: 'GET',
                url: '/getSource',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getType(select_id) {
            $.ajax({
                type: 'GET',
                url: '/getType',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getMode(select_id) {
            $.ajax({
                type: 'GET',
                url: '/getResponse',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getEtat(select_id) {
            $.ajax({
                type: 'GET',
                url: '/getEtat',
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                            $('#' + select_id).select2();
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function getreturn() {
            history.back();
            window.location = '/';
        }

        function reset_form() {
            $('select').select2().val("").trigger("change");
            easyMDE.value('');
        }

        function _reload_dt_contacts() {
            _loadcontacts(['contact']);
        }

        $("form#addtask").submit(function(e) {
            e.preventDefault();

            
            //Add the modal send email
            var modal_id = 'modal_form_mail';
            var modal_content_id = 'modal_form_mail_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            // var invoice_id = 2553
            // $.ajax({
            //     url: "/form/mail/invoice/" + invoice_id,
            //     type: "GET",
            //     dataType: "html",
            //     success: function(html, status) {
            //         $("#" + modal_content_id).html(html);
            //     },
            // });

            //End of the modal send email

            //Old code

            // $('[data-onloading=true]').show();
            // $('[data-onloading=false]').hide();
            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: '/createTask' ,
                data: formData,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(html  , result) {
                    // if (result.success) {
                    //     0.
                    //     .$
                    console.log('ok')
                    console.log(html)
                    $("#" + modal_content_id).html(html);

                    let responsable_entity = $("#responsable").select2().val();
                    $("#entitie_id").val(responsable_entity);
                    let new_responsable_entity = $("#responsable").select2().val();
                    let selected_contact_id = $('#selected_contact_id').val();

                    console.log('entity hna '+new_responsable_entity);
                    console.log('contact hna '+selected_contact_id);

                    _loadContactsForSelectOptions('selectContacts', new_responsable_entity, selected_contact_id);
                    // $('html,body').scrollTop(0);
                    // $("#popup1").show();
                    // $('input[type="reset"]').click();
                    // }
                    // $('[data-onloading=true]').hide();
                    // $('[data-onloading=false]').show();
                }
            });

            //End old code

            // if (resume == "" || rapporteurtext == "") {
            //     $("#popup").show();
            //     $("#popup1").hide();
            //     $('html,body').scrollTop(0);

            // } else {
            //     $("#popup").hide();

            //     // $.ajaxSetup({
            //     //     headers: {
            //     //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     //     }
            //     // });

            // }
            // for (const [key, value] of formData) {
            //     console.log(`${key}: ${value}\n`);
            // }

            //Old code || Get the selected value of "responsable" select element

            // var responsableValue = $('#rapporteur').val();

            // Verify if "responsable" is empty
            // if (responsableValue != "") {
            //         Swal.fire({
            //         title: "Avertissement",
            //         text: "Attention, vous n'avez pas sélectionné de responsable.",
            //         icon: "warning",
            //         showCancelButton: true,
            //         confirmButtonText: "Continuer sans responsable",
            //         cancelButtonText: "Annuler"
            //     }).then((result) => {
            //         if (result.value) {
            //             $.ajax({
            //             type: 'POST',
            //             url: '/createTask',
            //             data: formData,
            //             dataType: 'JSON',
            //             processData: false,
            //             contentType: false,
            //             success: function(result) {
            //                 if (result.success) {
            //                     0.
            //                     .$
            //                 $('html,body').scrollTop(0);
            //                 $("#popup1").show();
            //                 $('input[type="reset"]').click();
            //                 }
            //                 $('[data-onloading=true]').hide();
            //                 $('[data-onloading=false]').show();
            //             }
            //             });
            //         }
            //     });
            // }

        });


        // $("form#addtask").submit(function(e) {
        //     e.preventDefault();
        //     $('[data-onloading=true]').show();
        //     $('[data-onloading=false]').hide();
        //     var formData = new FormData(this);

        //     // if (resume == "" || rapporteurtext == "") {
        //     //     $("#popup").show();
        //     //     $("#popup1").hide();
        //     //     $('html,body').scrollTop(0);

        //     // } else {
        //     //     $("#popup").hide();

        //     //     // $.ajaxSetup({
        //     //     //     headers: {
        //     //     //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     //     //     }
        //     //     // });

        //     // }
        //     // for (const [key, value] of formData) {
        //     //     console.log(`${key}: ${value}\n`);
        //     // }

        //     // Get the selected value of "responsable" select element
        //     var responsableValue = $('#responsable').val();

        //     // Verify if "responsable" is empty
        //     if (responsableValue === "") {
        //             Swal.fire({
        //             title: "Avertissement",
        //             text: "Attention, vous n'avez pas sélectionné de responsable.",
        //             icon: "warning",
        //             showCancelButton: true,
        //             confirmButtonText: "Continuer sans responsable",
        //             cancelButtonText: "Annuler"
        //         }).then((result) => {
        //             if (result.value) {
        //                 $.ajax({
        //                 type: 'POST',
        //                 url: '/createTask',
        //                 data: formData,
        //                 dataType: 'JSON',
        //                 processData: false,
        //                 contentType: false,
        //                 success: function(result) {
        //                     if (result.success) {
        //                     $('html,body').scrollTop(0);
        //                     $("#popup1").show();
        //                     $('input[type="reset"]').click();
        //                     }
        //                     $('[data-onloading=true]').hide();
        //                     $('[data-onloading=false]').show();
        //                 }
        //                 });
        //             }
        //         });
        //     } 
        // });

        $("#contact_add").click(function(e) {
            e.preventDefault();
            _formContact(0, 0);
        });

        $("#entity_add").click(function(e) {
            e.preventDefault();
            _formEntity(0);
        });

        $("#entity_search").click(function(e) {
            e.preventDefault();
            _entitiesDatatable();
            var modal_id = 'modal_form_contact';
            // $('#' + modal_id).modal('show');
        });

        $("input[name=motivation]").change(function(e) {
            if (this.value == 'contact') {
                $(".contact-card").slideDown();
                // _loadcontacts(['rapporteur', 'responsable']);
            } else {
                $(".contact-card").slideUp();
                // _loadcontacts(['rapporteur', 'responsable'], true);
            }
        });

        $('#contact').change(function(e) {
            const contact_id = $(this).val();
            if (!contact_id || contact_id == '') {
                return false;
            }
            $.ajax({
                url: '/api/getcontactdata/' + contact_id,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.adresse != null) {
                        var address = (data.adresse.line_1 ? data.adresse.line_1 : "") + 
                        (data.adresse.line_2 ? "\n" + data.adresse.line_2 : "") +
                        (data.adresse.line_3 ? "\n" + data.adresse.line_3 : "");
                        
                        $('#contact_address').val(address.replace(/null/g, ""));
                        $('#contact_cp').val(data.adresse.postal_code?data.adresse.postal_code:"");
                        $('#contact_city').val(data.adresse.city?data.adresse.city:"");
                    }
                    $('#contact_phone').val(data.pro_phone?data.pro_phone:"");
                    $('#contact_email').val(data.email?data.email:"");
                },
            });
        });

        $('#entity').change(function(e, contact_id) {
            const entity_id = $(this).val();
            if (!entity_id || entity_id == '') {
                return false;
            }
            _loadcontacts(['contact'], false, entity_id, contact_id);
        });

        var _formContact = function(contact_id, entity_id) {
            var modal_id = 'modal_form_contact';
            var modal_content_id = 'modal_form_contact_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/contact/' + contact_id + '/' + entity_id + '?withuser=1&allentities=1',
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

        var _formEntity = function(entity_id) {
            var modal_id = 'modal_form_entitie';
            var modal_content_id = 'modal_form_entitie_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/entitie/' + entity_id,
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

        var _entitiesDatatable = function() {
            var modal_id = 'modal_dt_entities';
            var modal_content_id = 'modal_dt_entities_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/api/sdt/deepsearch/entities',
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                    $('#' + modal_id).modal('show');
                },
            });
        }

        //define date 
        var date = new Date();

        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();

        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;

        var today = day + "/" + month + "/" + year;
        document.getElementById("datedebut").value = today;
        /* Load entities deep seaarch modal  */
    </script>
@endsection
