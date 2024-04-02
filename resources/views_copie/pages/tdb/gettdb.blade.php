{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="card-title">
    <h3 class="card-label">
        STATISTIQUES : TDB Exportation
    </h3>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card card-custom card-stretch p-5">
            <div class="d-flex justify-content-between align-items-end">
                <h3>TDB Intervenants</h3>
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i> Télécharger</button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="{{ url('/api/sdt/tdb/exportIntervenants') }}" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-excel-o"></i>
                                    </span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom card-stretch p-5">
            <div class="d-flex justify-content-between align-items-end">
                <h3>TDB Activités</h3>
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i> Télécharger</button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="{{ url('/api/sdt/tdb/exportActivites') }}" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-excel-o"></i>
                                    </span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom card-stretch p-5">
            <div class="d-flex justify-content-between align-items-end">
                <h3>TDB Etudiants</h3>
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i> Télécharger</button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <form id="getStudentsSheet" method="GET" action="{{ url('/api/sdt/tdb/exportEtudiants') }}">
                                    <input type="hidden" value="" name="date_debut" id="date_debut"/>
                                    <input type="hidden" value="" name="date_fin" id="date_fin"/>
                                <a href="#" class="navi-link" id="downloadStudent">
                                    <span class="navi-icon">
                                        <i class="la la-file-excel-o"></i>
                                    </span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </form>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-10">

    </div>
</div>

<br>
<div class="row">
    <div class="col-lg-6">
        <div class="card-title">
            <h3 class="card-label">
                Filtre : TDB Intervenants
            </h3>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-title">
            <h3 class="card-label">
                Filtre : TDB Etudiants
            </h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="input-daterange input-group" id="filter_datepicker">
            <input type="text" class="form-control datatable-input" name="filter_start" id="filter_start_intervenants"
                value="" placeholder="Du" data-col-index="5" autocomplete="off" />
            <div class="input-group-append">
                <span class="input-group-text">
                    <i class="la la-ellipsis-h"></i>
                </span>
            </div>
            <input type="text" class="form-control datatable-input" name="filter_end" id="filter_end_intervenants" value=""
                placeholder="Au" data-col-index="5" autocomplete="off" />
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="input-daterange input-group" id="filter_datepicker">
            <input type="text" class="form-control datatable-input" name="filter_start_std" id="filter_start_etudiants"
                value="" placeholder="Du" data-col-index="5" autocomplete="off" />
            <div class="input-group-append">
                <span class="input-group-text">
                    <i class="la la-ellipsis-h"></i>
                </span>
            </div>
            <input type="text" class="form-control datatable-input" name="filter_end_std" id="filter_end_etudiants" value=""
                placeholder="Au" data-col-index="5" autocomplete="off" />
        </div>
    </div>
</div>
@endsection

{{-- Scripts Section --}}
@section('scripts')
<script>

$("#filter_start_etudiants").on("change", function() {
        var startDateValue = $(this).val();
        let start_elem = $("#date_debut");
        start_elem.val(startDateValue);
        // console.log("Selected Start Date: " + startDateValue);
        // You can perform further actions with the start date value here
    });

    $("#filter_end_etudiants").on("change", function() {
        var endDateValue = $(this).val();
        let end_elem = $("#date_fin");
        end_elem.val(endDateValue);

        // console.log("Selected End Date: " + endDateValue);
        // You can perform further actions with the end date value here
    });
   
    $("#downloadStudent").click(function(event)
    {
        event.preventDefault();
        $("#getStudentsSheet").submit();
    });
    $('#filter_start_intervenants,#filter_end_intervenants,#filter_start_etudiants,#filter_end_etudiants').datepicker({
        language: 'fr',
        rtl: KTUtil.isRTL(),
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
</script>
@endsection