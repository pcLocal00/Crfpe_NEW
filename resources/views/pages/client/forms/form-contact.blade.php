@php
    $modal_title=($row)?'Edition contact':'Ajouter un contact';
    $createdAt = $updatedAt = $deletedAt = '';
    if($row){
    $createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
    $updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
    $deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
    }
    $checkedMainContact = ($row && $row->is_main_contact===1)?'checked="checked"':'';
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contact_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form contact : begin -->
<form id="formContact" class="form">
    <input type="hidden" name="withuser" value="{{$withuser}}">
    <div class="modal-body" id="modal_form_contact_body">
        <div data-scroll="true" data-height="550">
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
            <div class="row">
                @if(!$entity)
                    <div class="col-lg-12">
                        <div class="form-group">
                            <input type="hidden" id="selected_entitie_id" value="{{ ($entity)?$entity->id:0 }}">
                            <label>Client
                                @if(!$row)<span class="text-danger">*</span>@endif
                                <span id="LOADER_ENTITIES"></span></label>
                            <div @if($row && $row->entitie)class="d-none" @endif>
                                <select class="form-control select2" id="entitiesSelect" name="entitie_id" required>
                                    <option value="">Sélectionnez un client</option>
                                </select>
                            </div>

                        </div>
                    </div>
                @else
                    <input type="hidden" id="entity_id" name="entitie_id" value="{{($entity)?$entity->id:0}}">
                @endif

                @if($row && $row->entitie)
                    <p class="text-primary">
                        <input type="hidden" name="entitie_id" id="input_entitie_id"
                               value="{{ $row->entitie->id }}"/>
                        {{ $row->entitie->name.' - '.$row->entitie->ref.' - '.$row->entitie->entity_type }}
                    </p>
                @endif

            </div>
            <input type="hidden" name="c_id" id="INPUT_CONTACT_ID_HELPER" value="{{ ($row)?$row->id:0 }}"/>
            <div id="FORM_BLOCK">

            </div>
            <!--end::contact form-->
        </div>
    </div>
    @if(auth()->user()->roles[0]->code!='FORMATEUR')    
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
    @endif
</form>
<!-- Form contact : end -->
<script>
    var selected_entitie_id = $('#selected_entitie_id').val();
    @if(!$row)
    _loadDatasEntitiesForSelectOptions('entitiesSelect', 0, `{{ $allentities ? 'A' : 'S' }}`, 2, selected_entitie_id);
    @endif
    $('#entitiesSelect').select2();

    function _loadDatasEntitiesForSelectOptions(select_id, entity_id, entity_type, is_former, selected_value = 0) {
        _showLoader('LOADER_ENTITIES');
        $.ajax({
            url: '/api/select/options/entities/type/' + entity_type,
            dataType: 'json',
            success: function (response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                            "</option>");
                    }
                }
            },
            error: function (x, e) {
            }
        }).done(function () {
            if (selected_value != 0 && selected_value != '') {
                $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
            }
            _hideLoader('LOADER_ENTITIES');
        });
    }

    $('#entitiesSelect').on('change', function () {
        _loadContactForm();
    });

    var contact_id = $('#INPUT_CONTACT_ID_HELPER').val();
    //console.log(contact_id);
    if (contact_id > 0 || $('#entity_id').val() > 0) {
        _loadContactForm();
    }

    function _loadContactForm() {
        var contact_id = $('#INPUT_CONTACT_ID_HELPER').val();
        if (contact_id == 0) {
            if ($('#entity_id').val() > 0) {
                var entity_id = $('#entity_id').val();

            } else {
                var entity_id = $('#entitiesSelect').val();
            }
        } else {
            if (entity_id > 0) {
                var entity_id = $('#entity_id').val();
            } else {
                var entity_id = $('#input_entitie_id').val();
            }

        }

        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#FORM_BLOCK').html(spinner);
        $.ajax({
            url: '/form/contact/component/' + contact_id + '/' + entity_id,
            type: 'GET',
            dataType: 'html',
            success: function (html, status) {
                $('#FORM_BLOCK').html(html);
            },
            error: function (result, status, error) {
            },
            complete: function (result, status) {
            }
        });
    }
</script>
