@if($sheet)

<div class="card card-custom card-fit card-border">
    <div class="card-body p-4">
        <div class="col-md-12">
            <p>Crée le : <span
                    class="label label-outline-warning label-pill label-inline">{{ $sheet->created_at->format('d/m/Y H:i') }}</span>
                - Modifiée le : <span
                    class="label label-outline-warning label-pill label-inline">{{ $sheet->updated_at->format('d/m/Y H:i') }}</span>
                - Etat : <span
                    class="label label-outline-warning label-pill label-inline">{{ $sheet->state->name }}</span>
            </p>
        </div>
        <div class="col-md-12">
            <p><!-- Fiche n° : <span class="label label-outline-warning">{{ $sheet->id }}</span> - --> Code : <span
                    class="label label-outline-warning label-pill label-inline">{{ $sheet->ft_code }}</span> - Version :
                <span class="label label-outline-warning">{{ $sheet->version }}</span>
            </p>
        </div>
        <div class="col-md-12">
        <p>Description : </p>
        {!! $sheet->description !!}
        </div>
    </div>
</div>

<!--begin::nav-->
<div class="card card-custom card-fit card-border mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                <ul class="nav flex-column nav-pills">
                    @if($sheetParams)
                    @foreach($sheetParams as $key=>$sp)
                    <li class="nav-item mb-2">
                        <a class="nav-link @if($key==0)active @endif" id="sheetparam-tab-{{ $sp->id }}"
                            data-toggle="tab" href="#sheetparam-{{ $sp->id }}">
                            <span class="nav-icon">
                                <i class="flaticon2-chat-1"></i>
                            </span>
                            <span class="nav-text">{{ $sp->title }}</span>
                        </a>
                    </li>
                    @endforeach
                    @endif
                </ul>
            </div>
            <div class="col-8">
                <div class="tab-content">

                    @if($sheetParams)
                    @foreach($sheetParams as $key=>$sp)
                    <div class="tab-pane fade @if($key==0)show active @endif" id="sheetparam-{{ $sp->id }}"
                        role="tabpanel" aria-labelledby="sheetparam-tab-{{ $sp->id }}">
                        <!--begin::Card-->
                        <div class="card card-custom card-fit card-border">
                            <div class="card-body">
                                {!! $sp->content !!}
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                    @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Card-->
<!--end::nav-->
@else
<div class="alert alert-custom alert-outline-danger fade show mb-5" role="alert">
    <div class="alert-icon">
        <i class="flaticon-warning"></i>
    </div>
    <div class="alert-text">Aucune fiche technique n'est définie pour cet action de formation</div>
</div>
@endif