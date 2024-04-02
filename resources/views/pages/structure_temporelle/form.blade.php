@php
    $modal_title=($row)?'Edition structure temporelle':'Ajouter une structure temporelle';
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
<form id="formStructure" class="form">
    <div class="modal-body" id="modal_form_structure_body">
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
            <input type="hidden" id="INPUT_ID_STRUCTURE" name="id" value="{{ ($row)?$row->id:0 }}"/>

            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionformCategorie">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseCatalogue">
                                    <i class="flaticon-file-1"></i> Structure Temporelle
                                </div>
                            </div>
                            <div id="collapseCatalogue" class="collapse show" data-parent="#accordionformCategorie">
                                <div class="card-body">

                                @if(!$row)
                                    <!--Modele-->
                                        <div class="row">
                                            <div class="col-lg-10">
                                                <div class="form-group">
                                                    <input type="hidden" id="selected_model_id"
                                                           name="model_id"
                                                           value="{{ ($model)?$model->id:0 }}">
                                                    <label>Modèle
                                                        @if(!$row)<span class="text-danger">*</span>@endif
                                                        <span id="LOADER_MODELS"></span></label>
                                                    <div @if($row && $row->model)class="d-none" @endif>
                                                        <select class="form-control select2" id="modelsSelect"
                                                                name="model_id" required>
                                                            <option value="">Sélectionnez un modèle</option>
                                                        </select>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


                                    @else

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <p class="text-primary">
                                                        <input type="hidden" id="selected_model_id"
                                                               name="model_id"
                                                               value="{{ ($row)?$row->model->id:0 }}">
                                                        {{ $row->model->code.' - '.$row->model->name}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                @endif


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

                                    <!--Parent-->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="hidden" id="selected_parent_id"
                                                       name="parent_id"
                                                       value="{{ ($row)?$row->parent_id:0 }}">
                                                <label>Parent
                                                    @if(!$row)<span class="text-danger">*</span>@endif
                                                    <span id="LOADER_PARENTS"></span></label>
                                                <div>
                                                    <select class="form-control select2" id="parentsSelect"
                                                            name="parent_id">
                                                        <option value="">Sélectionnez un parent</option>
                                                    </select>
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
                id="BTN_SAVE_STRUCTURE"></span></button>
    </div>
</form>
<!-- Form structure : end -->
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var selected_model_id = $('#selected_model_id').val();
    var selected_parent_id = $('#selected_parent_id').val();
    @if(!$row)
    _loadDatasModelsForSelectOptions('modelsSelect', 0, selected_model_id);
    @endif
    _loadDatasParentsForSelectOptions('parentsSelect', 0, selected_parent_id);

    $('.select2').select2();

    function _loadDatasModelsForSelectOptions(select_id, model_id, selected_value = 0) {
        _showLoader('LOADER_MODELS');
        $.ajax({
            url: '/api/select/options/models/' + model_id,
            dataType: 'json',
            success: function (response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].code_name +
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
            _hideLoader('LOADER_MODELS');
        });
    }

    function _loadDatasParentsForSelectOptions(select_id, parent_id = 0, selected_value = 0) {
        _showLoader('LOADER_PARENTS');
        $.ajax({
            url: '/api/select/options/parents/' + parent_id,
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
            _hideLoader('LOADER_PARENTS');
        });
    }


    $("#formStructure").validate({
        rules: {},
        messages: {},
        submitHandler: function (form) {
            _showLoader('BTN_SAVE_STRUCTURE');
            $.ajax({
                type: 'POST',
                url: '/form/structure',
                data: $(form).serialize(),
                dataType: 'JSON',
                success: function (result) {
                    _hideLoader('BTN_SAVE_STRUCTURE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_form_structure').modal('hide');
                        resfreshJSTreeStructures(0);
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function (error) {
                    _hideLoader('BTN_SAVE_STRUCTURE');
                    _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
                },
                complete: function (resultat, statut) {
                    _hideLoader('BTN_SAVE_STRUCTURE');
                    resfreshJSTreeStructures(0);
                }
            });
            return false;
        }
    });


</script>
<script src="{{ asset('custom/js/form-model.js?v=3') }}"></script>
