@php
    $modal_title=($group)?'Edition groupe':'Ajouter un groupe';
    $createdAt = $updatedAt = $deletedAt = '';
    if($group){
    $createdAt = ($group->created_at)?$group->created_at->format('d/m/Y H:i'):'';
    $updatedAt = ($group->updated_at)?$group->updated_at->format('d/m/Y H:i'):'';
    $deletedAt = ($group->deleted_at)?$group->deleted_at->format('d/m/Y H:i'):'';
    }
    $checkedMainContact = ($group && $group->is_main_contact===1)?'checked="checked"':'';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contact_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<!-- Form group : begin -->
<form id="formGroup" class="form">
    <div class="modal-body" id="modal_form_group_body">
        <div data-scroll="true" data-height="550">
        @if($group)
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
                <!-- Store ID group if exist (on update) -->
                <input name="id" type="hidden" value="{{$group->id}}">


        @endif


        <!-- titre -->
            <div class="row">
                <div class="col-lg-12">
                    @php
                        $defaultTitle = ($group && $group->title)?$group->title:'';
                    @endphp
                    <div class="form-group">
                        <label for="title">Titre : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="title"
                               value="{{ ($group)?$group->title:$defaultTitle }}" id="title" required/>
                    </div>
                    <div class="form-group">
                        <label>Référent :</label>
                        <select class="form-control datatable-input" data-col-index="2" id="groupesSelect"
                            name="referance_id">
                            <option value="0">Tous</option>
                            @if(isset($referances))
                                @foreach ($referances as $referance)
                                <option value="{{ $referance->contact->id }}">{{ $referance->contact->firstname }} {{ $referance->contact->lastname }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <!-- end titre -->


            <!-- Action -->

            <input type="hidden" name="af_id" id="input_action_id"
                   value="{{ $af->id }}"/>

            <!-- end action -->

            <!--end::contact form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>


</form>
<!-- Form group : end -->


<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#formGroup").validate({
        rules: {},
        messages: {},
        submitHandler: function (form) {
            _showLoader('BTN_SAVE');
            var formData = $(form).serializeArray();
            $.ajax({
                type: 'POST',
                url: '/form/group',
                data: formData,
                dataType: 'JSON',
                success: function (result) {
                    _hideLoader('BTN_SAVE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_form_group').modal('hide');
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function (error) {
                    _hideLoader('BTN_SAVE');
                    _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
                },
                complete: function (resultat, statut) {
                    _hideLoader('BTN_SAVE');
                    _reload_dt_groups();

                }
            });
            return false;
        }
    });


</script>
