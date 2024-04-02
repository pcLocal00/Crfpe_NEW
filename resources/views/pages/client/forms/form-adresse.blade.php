@php
$modal_title=($row)?'Edition adresse':'Ajouter une adresse';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_adresse_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form contact : begin -->
<form id="formAdresse" class="form">
    <div class="modal-body" id="modal_form_adresse_body">
        <div data-scroll="true" data-height="600">
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
            @endif
            <input type="hidden" name="a_id" id="INPUT_ADRESSE_ID_HELPER" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="a_entitie_id" value="{{ ($row)?$row->entitie_id:$entity_id }}" />
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <div class="checkbox-inline">
                            @php
                            $checkedMainAdresse = ($row && $row->is_main_entity_address===1)?'checked="checked"':'';
                            @endphp
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="a_is_main_entity_address"
                                    {{ $checkedMainAdresse }}>
                                <span></span>Principale</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        @php
                        $checkedBilling = ($row && $row->is_billing===1)?'checked="checked"':'checked="checked"';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="a_is_billing" {{ $checkedBilling }}>
                                <span></span>Facturation</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="checkbox-inline">
                        @php
                        $checked = ($row && $row->is_stage_site===1)?'checked="checked"':'';
                        @endphp
                        <label class="checkbox">
                            <input type="checkbox" value="1" name="a_is_stage_site" {{ $checked }}>
                            <span></span>Terrain de stage</label>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        @php
                        $checkedFS = ($row && $row->is_formation_site===1)?'checked="checked"':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="a_is_formation_site" {{ $checkedFS }}>
                                <span></span>Lieu de formation</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mb-2">
                        <label for="line_1">Ligne 1<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="a_line_1" id="line_1" rows="3"
                            required>{{ ($row)?$row->line_1:'' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mb-2">
                        <label for="line_2">Ligne 2</label>
                        <textarea class="form-control" name="a_line_2" id="line_2"
                            rows="3">{{ ($row)?$row->line_2:'' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group mb-2">
                        <label for="line_3">Ligne 3</label>
                        <textarea class="form-control" name="a_line_3" id="line_3"
                            rows="3">{{ ($row)?$row->line_3:'' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Code postal</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="ZIPCODE" name="a_postal_code" value="{{ ($row)?$row->postal_code:'' }}" />
                            <div class="input-group-append">
                                <button type="button" onclick="_call_api_to_search_cities()" data-toggle="tooltip"
                                    title="Rechercher les villes" class="btn btn-icon btn-outline-primary"><span
                                        id="BTN_SEARCH_CITIES"><i class="flaticon2-search"></i></span></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Ville <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="a_city" value="{{ ($row)?$row->city:'' }}"
                            required />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="paysSelects">Pays</label>
                        @php
                        $selected_country = ($row)?$row->country:'';
                        @endphp
                        <select class="form-control " name="a_country" id="paysSelects">
                            <option value="0">Pays</option>
                            @if(count($countriesDatas)>0)
                                @foreach($countriesDatas as $dt)
                                    <option value="{{$dt['code']}}" {{($selected_country == $dt['code'])? 'selected' : '' }}>{{$dt['country']}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <!-- end::adresse form -->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form contact : end -->
<script src="{{ asset('custom/js/form-adresse.js?v=2') }}"></script>