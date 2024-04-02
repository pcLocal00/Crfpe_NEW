@php
    $modal_title=($row)?'Edition modèle':'Ajouter un modèle';
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

<!-- Form structure : begin -->
<form id="formModel" class="form">
    <div class="modal-body" id="modal_form_model_body">
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
            <input type="hidden" id="INPUT_ID_MODEL" name="id" value="{{ ($row)?$row->id:0 }}"/>

            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionformCategorie">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseCatalogue">
                                    <i class="flaticon-file-1"></i> Modèle
                                </div>
                            </div>
                            <div id="collapseCatalogue" class="collapse show" data-parent="#accordionformCategorie">
                                <div class="card-body">

                                    <!--Code-->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control " id="ID_NAME_STRUCTURE"
                                                       minlength="3" name="code" value="{{ ($row)?$row->code:'' }}"
                                                       required/>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Nom-->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Nom <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control " id="ID_NAME_STRUCTURE"
                                                       minlength="3" name="name" value="{{ ($row)?$row->name:'' }}"
                                                       required/>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Ordre-->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Ordre hérarchique</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="order_show"
                                                           id="ID_ORDER_STRUCTURE"
                                                           value="{{($row)?$row->sort:5}}"/>
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
                                                        $row->is_active===1)?'checked="checked"':'';
                                                    @endphp
                                                    <label class="checkbox">
                                                        <input type="checkbox" value="1" name="is_active"
                                                            {{ $checked }}>
                                                        <span></span>Active</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_MODEL"></span></button>
    </div>
</form>
<!-- Form structure : end -->
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


</script>
<script src="{{ asset('custom/js/form-model.js?v=3') }}"></script>
