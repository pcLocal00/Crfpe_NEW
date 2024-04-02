@php
$modal_title=($row)?'Edition ressource':'Ajouter une ressource';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_ressource_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->
<form id="formRessource" class="form">
    <div class="modal-body" id="modal_form_ressource_body">
        <div data-scroll="true" data-height="400">
            @csrf
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
            <input id="INPUT_HIDDEN_ID_RESSOURCE" type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input id="is_active" type="checkbox" value="1" name="is_active"
                                    {{ ($row && $row->is_active===1)?'checked="checked"':'' }}>
                                <span></span>Activé</label>
                            <label class="checkbox">
                                <input id="is_dispo" type="checkbox" value="1" name="is_dispo"
                                    {{ ($row && $row->is_dispo===1)?'checked="checked"':'' }}>
                                <span></span>Disponibilité</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Type de ressource</label>
                        <select class="form-control" name="type" id="select_type">
                            @if($ressource_types)
                            @foreach($ressource_types as $param)
                            @php
                            $selected_type = ($row && $row->type===$param['code'])?'selected':'';
                            @endphp
                            <option value="{{ $param['code'] }}" {{ $selected_type }}>{{ $param['name'] }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ ($row)?$row->name:'' }}"
                            required />
                    </div>
                </div>
            </div>
            <div class="row d-none" id="INTERNAL_EXTERNAL_BLOCK">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="radio-inline">
                                        <label class="radio">
                                            <input type="radio" {{ ($row && $row->is_internal===1)?'checked="checked"':'' }} name="is_internal" value="1">
                                            <span></span>Interne</label>
                                        <label class="radio">
                                            <input type="radio" {{ ($row && $row->is_internal===0)?'checked="checked"':'' }} name="is_internal" value="0">
                                            <span></span>Externe</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <!-- address_training_location -->
                                <div class="form-group mb-0">
                                    <label for="address_training_location">Adresse :</label>
                                    <textarea class="form-control" id="address_training_location" name="address_training_location" rows="3">{{ ($row)?$row->address_training_location:'' }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Ressource parente : </label>
                        <input type="hidden" id="selected_ressource_parent" value="{{ ($row)?$row->ressource_id:0 }}">
                        <select class="form-control select2" id="ressourcesParentSelect" name="ressource_id">
                            <option value="">Pas de ressource parente</option>
                        </select>
                        <span class="form-text text-muted">Cas d'une ressource qui peut se diviser.</span>
                        <script>
                        var selected_ressource_parent = $('#selected_ressource_parent').val();
                        var res_id = $('#INPUT_HIDDEN_ID_RESSOURCE').val();
                        var type = $('#select_type').val();
                        _loadDatasRessourcesForSelectOptions('ressourcesParentSelect', res_id, type,
                            selected_ressource_parent);
                        $('#ressourcesParentSelect').select2();
                        </script>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form : end -->
<script src="{{ asset('custom/js/form-ressource.js?v=1') }}"></script>
