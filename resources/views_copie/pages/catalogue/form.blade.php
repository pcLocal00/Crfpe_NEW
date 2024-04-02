@php
$modal_title=($row)?'Edition catégorie':'Ajouter une catégorie';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}

@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_catalogue_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form categorie : begin -->
<form id="formCategorie" class="form">
    <div class="modal-body" id="modal_form_catalogue_body">
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
                        <div class="alert-text">Archivé le : {{ $deletedAt }} - Motif :
                            {{ ($row)?$row->archival_reason:'--' }}</div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Infos date : end -->
            @endif

            @csrf
            <input type="hidden" id="INPUT_ID_CATEGORIE" name="id" value="{{ ($row)?$row->id:0 }}" />

            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionformCategorie">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseCatalogue">
                                    <i class="flaticon-file-1"></i> Grain
                                </div>
                            </div>
                            <div id="collapseCatalogue" class="collapse show" data-parent="#accordionformCategorie">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group row align-items-center">
                                                <div class="col-lg-12">
                                                    <div class="checkbox-inline">
                                                        @php
                                                        $checked = ($row && $row->is_active===1)?'checked="checked"':'';
                                                        @endphp
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="1" name="is_active"
                                                                {{ $checked }}>
                                                            <span></span>Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group row align-items-center">
                                                <div class="col-lg-12">
                                                    <div class="checkbox-inline">
                                                        @php
                                                        $checked = ($row &&
                                                        $row->site_broadcast===1)?'checked="checked"':'';
                                                        $requiredSiteName = ($row && $row->site_broadcast===1)?1:0;
                                                        @endphp
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="1" name="site_broadcast"
                                                                {{ $checked }} id="checkbox_broadcast">
                                                            <span></span>Diffusion sur le site internet</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Ordre d'affichage</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="order_show"
                                                        id="ID_ORDER_CATEGORIE"
                                                        value="{{ ($row)?$row->order_show:$default_order_show }}" />
                                                    <div class="input-group-append">

                                                        <button type="button" onclick="_generateOrderShowCategorie()"
                                                            data-toggle="tooltip" title="Générer un ordre"
                                                            class="btn btn-icon btn-outline-primary"><span
                                                                id="BTN_GERERATE_ORDER_CATEGORIE"><i
                                                                    class="flaticon2-reload"></i></span></button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Nom du grain <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control " id="ID_NAME_CATEGORIE"
                                                    minlength="3" name="name" value="{{ ($row)?$row->name:'' }}"
                                                    required />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Code <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" placeholder=""
                                                        id="ID_CODE_CATEGORIE" name="code"
                                                        value="{{ ($row)?$row->code:'' }}" required>
                                                    <div class="input-group-append">
                                                        <button type="button" onclick="_generateCodeCategorie()"
                                                            data-toggle="tooltip" title="Générer un code"
                                                            class="btn btn-icon btn-outline-primary"><span
                                                                id="BTN_GERERATE_CODE_CATEGORIE"><i
                                                                    class="flaticon2-reload"></i></span></button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Site internet -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="site_name">Nom sur le site internet <span
                                                        id="span_required_site_name"
                                                        class="@if($requiredSiteName)text-danger @endif">@if($requiredSiteName)*
                                                        @endif</span></label>
                                                <input type="text" class="form-control " id="input_site_name"
                                                    name="site_name" value="{{ ($row)?$row->site_name:'' }}"
                                                    @if($requiredSiteName)required @endif />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="description">Descriptif</label>
                                                <textarea class="form-control" id="description" name="description"
                                                    rows="3" required>{{ ($row)?$row->description:'' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionformParentCategorie">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseCatalogueParent">
                                    <i class="flaticon-map"></i> Catégorie parente
                                </div>
                            </div>
                            <div id="collapseCatalogueParent" class="collapse show"
                                data-parent="#accordionformParentCategorie">
                                <div class="card-body">
                                    <input type="hidden" id="categorie_id" name="categorie_id"
                                        value="{{ ($row)?$row->categorie_id:'' }}" />
                                    <div id="parent_categorie_tree" class="tree-demo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_CATEGORIE"></span></button>
    </div>
</form>
<!-- Form categorie : end -->
<script src="{{ asset('custom/js/form-catalogue.js?v=0') }}"></script>