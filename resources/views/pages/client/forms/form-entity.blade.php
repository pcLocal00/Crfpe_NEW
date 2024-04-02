@php
$modal_title=($row)?'Edition client':'Ajouter un client';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_entitie_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form categorie : begin -->
<div class="modal-body" id="modal_form_entitie_body">
    <div data-scroll="true" data-height="600">
        <form id="formEntitie" class="form">
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
            @endif


            <!-- Choix S or P -->
            @php
            $checkedSociete = '';
            $checkedParticulier = 'checked="checked"';
            if($row){
            if($row->entity_type=="S"){
            $checkedSociete = 'checked="checked"';
            }
            if($row->entity_type=="P"){
            $checkedParticulier = 'checked="checked"';
            }
            }
            @endphp
            <div class="row">
                <div class="col-lg-6">
                    <label class="option">
                        <span class="option-control">
                            <span class="radio">
                                <input type="radio" name="entity_type" value="P" {{ $checkedParticulier }} />
                                <span></span>
                            </span>
                        </span>
                        <span class="option-label">
                            <span class="option-head">
                                <span class="option-title">
                                    Particulier
                                </span>
                                <span class="option-focus">
                                    P
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
                <div class="col-lg-6">
                    <label class="option">
                        <span class="option-control">
                            <span class="radio">
                                <input type="radio" name="entity_type" value="S" {{ $checkedSociete }} />
                                <span></span>
                            </span>
                        </span>
                        <span class="option-label">
                            <span class="option-head">
                                <span class="option-title">
                                    Société
                                </span>
                                <span class="option-focus">
                                    S
                                </span>
                            </span>
                        </span>
                    </label>
                </div>

            </div>

            <!-- Choix S or P -->

            <!-- begin::hidden input helper -->
            <input type="hidden" id="INPUT_ENTITY_ID_HELPER" value="{{ ($row)?$row->id:0 }}" />
            @csrf
            <!-- Form en fonction de P ou S -->
            <div class="card">
                <div class="card-body" id="BLOCK_FORM">

                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formEntitie').submit();" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span id="BTN_SAVE_ENTITIE"></span></button>
</div>
<!-- Form categorie : end -->
<script src="{{ asset('custom/js/form-entity.js?v=1') }}"></script>