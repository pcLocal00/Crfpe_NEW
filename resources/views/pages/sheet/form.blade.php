@php
$modal_title=($sheet)?'Edition fiche technique':'Ajouter une fiche technique';
@endphp
<!-- begin::modal header -->
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_sheet_formation_title"><i class="flaticon-edit"></i> {{ $modal_title }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- end::modal header -->
<form id="form_sheet">
    <!-- begin::modal body -->
    <div class="modal-body">
        <div data-scroll="true" data-height="600">
            @csrf
            <div class="accordion accordion-solid accordion-toggle-plus" id="sheetAccordion1">
                <div class="card">
                    <div class="card-header" id="sheetHeadingOne6">
                        <div class="card-title" data-toggle="collapse" data-target="#sheetCollapseOne">
                            <i class="flaticon-file-1"></i> Détails de la fiche technique
                        </div>
                    </div>
                    <div id="sheetCollapseOne" class="collapse show" data-parent="#sheetAccordion1">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-12">

                                            <input type="hidden" name="id" value="{{ ($sheet)?$sheet->id:0 }}" />
                                            <input type="hidden" name="formation_id" value="{{ $formation_id }}" />

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Code :</label>
                                                <div class="col-8">
                                                    <input type="text" class="form-control form-control-sm" readonly
                                                        name="ft_code" placeholder=""
                                                        value="{{ ($sheet)?$sheet->ft_code:$generatedCode }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Version :</label>
                                                <div class="col-8">
                                                    <input type="number" class="form-control form-control-sm" readonly
                                                        placeholder="" name="version"
                                                        value="{{ ($sheet)?$sheet->version:$generatedVersion }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Etat :</label>
                                                <div class="col-8">
                                                    <select name="param_id" class="form-control form-control-sm">
                                                        @foreach ($state_params as $state)
                                                        @php
                                                        $selected = ($sheet && $sheet->param_id ===
                                                        $state["id"])?'selected':'';
                                                        @endphp
                                                        <option {{ $selected }} value="{{ $state["id"] }}">
                                                            {{ $state["name"] }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group row align-items-center">
                                                <div class="col-lg-12">
                                                    <div class="checkbox-inline">
                                                        @php
                                                            $checked = ($sheet && $sheet->is_default ===1)?'checked="checked"':'';
                                                        @endphp
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="1" name="is_default" {{ $checked }}>
                                                            <span></span>Fiche par défaut ?</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label for="sheet_description">Description :</label>
                                        <textarea class="form-control" id="sheet_description" name="description"
                                            rows="3">{{ ($sheet)?$sheet->description:(($defaultSheet)?$defaultSheet->description:'') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="sheetHeadingTwo6">
                        <div class="card-title collapsed" data-toggle="collapse" data-target="#sheetCollapseTwo">
                            <i class="flaticon-list-3"></i> Options de la fiche technique
                        </div>
                    </div>
                    <div id="sheetCollapseTwo" class="collapse" data-parent="#sheetAccordion1">
                        <div class="card-body">

                            <!--begin::nav-->
                            <div class="row">
                                <div class="col-4">
                                    <ul class="nav flex-column nav-pills">
                                        @if($params)
                                        @foreach($params as $key=>$arr)
                                        <li class="nav-item mb-2">
                                            <a class="nav-link @if($key==0)active @endif"
                                                id="sheetparam-tab-{{ $arr->id }}" data-toggle="tab"
                                                href="#sheetparam-{{ $arr->id }}">
                                                <span class="nav-icon">
                                                    <i class="flaticon2-chat-1"></i>
                                                </span>
                                                <span class="nav-text">{{ $arr->name }}</span>
                                            </a>
                                        </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div class="col-8">
                                    <div class="tab-content">
                                        @if($params)
                                        @foreach($params as $key=>$arr)
                                        <div class="tab-pane fade @if($key==0)show active @endif"
                                            id="sheetparam-{{ $arr->id }}" role="tabpanel"
                                            aria-labelledby="sheetparam-tab-{{ $arr->id }}">

                                            @php
                                                $sheetParamArray=($collectionSheetParams)?$collectionSheetParams->firstWhere('param_id',$arr->id):[];
                                                $defaultSheetParamArray=($collectionDefaultSheetParams)?$collectionDefaultSheetParams->firstWhere('param_id',$arr->id):[];
                                                $defaultContent=(count($defaultSheetParamArray)>0)?$defaultSheetParamArray['content']:'';
                                                $content=(count($sheetParamArray)>0)?$sheetParamArray['content']:$defaultContent;
                                            @endphp


                                            <textarea
                                                name="SHEET_PARAM[{{ $arr->id }}][{{ ($sheetParamArray && $sheetParamArray['id'])?$sheetParamArray['id']:0 }}]"
                                                class="sp-ckeditor" id="sp-ckeditor-{{ $arr->id }}"
                                                data-index="{{ $arr->id }}">{{$content}}</textarea>




                                        </div>
                                        @endforeach
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <!--end::nav-->

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end::modal body -->
    </div>
    <!--end::nav-->
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i>
            Valider <span id="BTN_SAVE_SHEET"></span></button>
    </div>
</form>
<script src="{{ asset('custom/js/view-sheet.js?v=3') }}"></script>