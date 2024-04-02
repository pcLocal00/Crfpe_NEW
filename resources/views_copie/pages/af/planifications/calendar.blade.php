{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

    <!-- Modal add new event -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modifier la Séance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <input type="hidden" name="Pp_id" id="Pp_id" value="{{$preplanning->id}}">
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Titre * :</span></div>
                            <div class="col-md-6 ms-auto"><input class="col form-control" type="text" id="title"></div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-6 d-flex align-items-center"><span>Numéro de séquence :</span></div>
                            <div class="col-md-2 d-flex justify-content-start ml-3 p-3 bg-primary text-white">
                                <input id="sequence_number" class="form-control input-lg text-center" type="text" value="">
                                <span class="align-self-center mx-2">/</span>
                                <input id="sequence_total" class="form-control input-lg text-center" type="text" value="">
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-6 d-flex align-items-center"><span>Commentaire :</span></div>
                            <div class="col-md">
                                <textarea name="" id="remarks"  class="form-control" id="" cols="30" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Date * :</span></div>
                            <div class="col-md-6 ms-auto">
                                <input id="date_start" class="form-control" type="text" value="" />
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Heure début * :</span></div>
                            <div class="col-md-6 ms-auto">
                                <input type='text' class="form-control" id='heure-debut' value="" />
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Heure fin * :</span></div>
                            <div class="col-md-6 ms-auto">
                                <input type='text' id="heure-fin" class="form-control" value=""/>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Groupe(s) * :</span></div>
                            <div class="col-md-8 ms-auto d-flex justify-content-between align-items-center">
                                <input type="hidden" class="form-control" id="group_id" value="0">
                                <input type="hidden" class="form-control" id="pp_schedule_id" value="0">
                                <select id="GroupeSelect"  name="group_id" class="form-control select2" style="width: 80%">
                                    {{-- <option value=""></option> --}}
                                </select>
                                <button class="btn btn-primary px-2 px-sm-2 py-0 py-sm-1" onclick="_updateGroupeSelect()">
                                    <i class="fa fa-circle-plus"></i> Ajouter
                                </button>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Regroupement(s) * :</span></div>
                            <div class="col-md-8 ms-auto d-flex justify-content-between align-items-center">
                                <input type="hidden" class="form-control" id="Regroupement_id" value="0">
                                {{-- <input type="hidden" class="form-control" id="pp_schedule_id" value="0"> --}}
                                <select id="RegroupementSelect"  name="Regroupement_id" class="form-control select2" style="width: 80%">
                                    {{-- <option value=""></option> --}}
                                </select>
                                <button class="btn btn-primary px-2 px-sm-2 py-0 py-sm-1" onclick="_updateRegroupementSelect()">
                                    <i class="fa fa-circle-plus"></i> Ajouter
                                </button>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-4 d-flex align-items-center"><span>Intervenant*:</span></div>
                            <div class="col-md-8 ms-auto d-flex justify-content-between align-items-center">
                                <input type="hidden" class="form-control" id="interv_id" value="0">
                                {{-- <input type="hidden" class="form-control" id="price" value="0"> --}}
                                <select id="SelectIntervenant" name="interv_id" class="form-control select2"  style="width: 80%">
                                    {{-- <option value="">--Sélectionner un Intervenant--</option> --}}
                                </select>
                                <button class="btn btn-primary px-2 px-sm-2 py-0 py-sm-1" onclick="_updateIntervenant()">
                                    <i class="fa fa-circle-plus"></i> Ajouter
                                </button>
                            </div>
                        </div>
                        {{-- display list : Intervenants + Rémunération --}}
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col"  class="border border-1 text-light px-3" style="background-color: rgb(65, 65, 230)">Intervenants</th>
                                    <th scope="col"  class="border border-1 text-light px-3" style="background-color: rgb(65, 65, 230)">Rémunération</th>
                                    <th scope="col"  class="border border-1 text-light px-3" style="background-color: rgb(65, 65, 230)">Type</th>
                                    <th scope="col"  class="border border-1 text-light px-3" style="background-color: rgb(65, 65, 230)">Action</th>
                                </tr>
                            </thead>
                            <tbody id="showIntervenants">
                                <tr>
                                    {{-- <td class="bg-info border border-1 px-3">Mark</td>
                                    <td class="bg-info border border-1 px-3">28€/h</td>
                                    <td class="bg-info border border-1 px-3">28€/h</td>
                                    <td class="bg-info border border-1 px-3">28€/h</td> --}}
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveBtn" class="btn btn-primary">Enregister</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" id="deleteBtn" class="btn btn-bg-danger">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal add new event-->
    {{-- End Intervenants modal --}}

    <div class="card card-custom">
        <div class="row mt-5 mt-ms-5 mb-3 mb-ms-5 mx-3">
            <div class="col-xl-1 d-flex align-items-center mt-3 mt-sm-2">
                <h5 style="color:#3699FF;">AF ciblé :</h5>
            </div>
            <div class="col-xl-3 justify-content-start align-items-center mt-3 mt-sm-0">
                {{-- <span class="px-3">AF ciblé </span> --}}
                <input type="hidden" class="form-control" id="af_id" value="{{ $preplanning->AF_target_id ?? '' }}">
                <div class="form-group">
                    <label></label>
                    <select id="AFOptions" name="af_id" onchange="_updatePreplanning({{$preplanning->id}})" class="form-control form-control-sm select2"
                    @if($preplanning->Nb_Sessions > 0)
                        disabled
                    @endif
                    >
                        <option value="">--AF ciblé----</option>
                    </select>
                </div>
            </div>
            <div class="col-xl-2 d-flex align-items-center mt-3 mt-sm-2">
                <h5 style="color:#3699FF;">Période de transfert du :</h5>
            </div>
            <div class="col-xl-4 d-flex justify-content-center align-items-center mt-3 mt-sm-0">
                @php
                $dtNow = Carbon\Carbon::now();
                $preplannings_start_date = $dtNow->format('d-m-Y');
                $preplannings_end_date = $dtNow->format('d-m-Y');
                @endphp
                <input type="text" class="form-control form-control-sm" name="start_date"
                    id="preplannings_start_date" placeholder="Sélectionner une date" value="{{ $preplannings_start_date }}"
                    autocomplete="off" required />
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="la la-calendar-check-o"></i>
                    </span>
                </div>
                <input type="text" class="form-control form-control-sm" name="end_date"
                    id="preplannings_end_date" placeholder="Sélectionner une date" value="{{ $preplannings_end_date}}"
                    autocomplete="off" required />
            </div>
            <div class="col-xl d-flex align-items-center">
                <button type="button" class="btn btn-secondary" onclick="_transfererPplanifications()">Transférer la Pré planifications</button>
            </div>
        </div>
        <div class="row mx-3">
            <div class="col-xl-8 order-last order-sm-first ">
                    <div id="calendar"></div>
            </div>
            <div class="col-xl-4  mb-5 mb-sm-0 order-first order-sm-last ">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation" >
                      <button style="background-color: cornflowerblue;" class="nav-link active text-light" id="Sessions-tab" data-bs-toggle="tab" data-bs-target="#Sessions" type="button" role="tab" aria-controls="Sessions" aria-selected="true">Sessions</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    {{-- sessions --}}
                    <div class="tab-pane fade show active" id="Sessions" role="tabpanel" aria-labelledby="Sessions-tab">
                        <input type="hidden" id="product_id" value="{{ $preplanning->PF_id }}" />
                        <div class="card-body">
                            <div id="structure_temporelle_tree" class="tree-demo"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Styles Section --}}
@section('styles')
<style>
    .fc-daygrid-event-dot {
        border: calc(var(--fc-daygrid-event-dot-width)/2) solid #fff !important;
    }
    .fc-daygrid-dot-event .fc-event-title {
        color: rgb(0, 0, 0);
    }
    .fc-direction-ltr .fc-daygrid-event .fc-event-time {
        margin-right: 3px;
        color: rgb(0, 0, 0);
        font-weight: 600;
    } 
    .fc-timegrid-event .fc-event-main {
        padding: 1px 1px 0;
        color: black !important;
        font-weight: 600 !important;
    }
</style>
<link href="{{ asset('/custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css" />
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" integrity="sha512-SbiR/eusphKoMVVXysTKG/7VseWii+Y3FdHrt0EpKgpToZeemhqHeZeLWLhJutz/2ut2Vw1uQEj2MbRF+TVBUA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.0.2/index.global.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.0.2/locales-all.global.min.js'></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>

<script>
    $('#date_start').datepicker({
        language: 'fr',
        format: 'yyyy-mm-dd',
        //todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
    $('#heure-debut').timepicker({
        showMeridian: false,
        language: 'fr',
    });
    $("#heure-fin").timepicker({
        showMeridian: false,
        language: 'fr',
    });
    $('.select2').select2();
    $('#preplannings_start_date').datepicker({
        language: 'fr',
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
    $('#preplannings_end_date').datepicker({
        language: 'fr',
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
    $('#datepicker').datepicker();

    function _transfererPplanifications(){
        var preplannings_start_date = $('#preplannings_start_date').val();
        var preplannings_end_date = $('#preplannings_end_date').val();
        var Ppreplanning_id = $('#Pp_id').val();
        //alert("preplannings_start_date :" +preplannings_start_date + "\npreplannings_end_date :" + preplannings_end_date + "\nPpreplanning_id :" + Ppreplanning_id)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url:"{{route('transPplanifications')}}",
            data: {
                preplannings_start_date:preplannings_start_date,
                preplannings_end_date:preplannings_end_date,
                Ppreplanning_id:Ppreplanning_id
            },
            dataType: 'JSON',
            success: function(response){
                var array = response;
                console.log(array);
                var content = "";
                if(array != ''){
                    content = '<table class="d-flex justify-content-center" ><tr><th>Titre</th><th>Date</th><th>Group/Regroupement</th><th>Formateur principal/type</th><th>Tarif</th></tr>';
                    for (i in array) {
                    content += '<tr><td class="bg-light border border-info border-1 px-3">' + array[i].title + '</td><td class="bg-light border border-info border-1 px-3">' + new Date(array[i].date_start).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + '</td><td class="bg-light border border-info border-1 px-3">' + array[i].group + '</td><td class="bg-light border border-info border-1 px-3">' + array[i].formateur + ' / '+array[i].formateur_type+'</td><td class="bg-light border border-info border-1 px-3">' + array[i].price + '</td></tr>';
                    }
                var url = '/pdf/transferCalendar/' + preplannings_start_date + '/' + preplannings_end_date + '/' + Ppreplanning_id;
                var footer = '<a href="' + url + '" class="btn btn-sm btn-icon btn-light-primary w-50 fs-4" data-toggle="tooltip" title="Télécharger la fiche technique"><i class="flaticon-download px-3"></i> Télécharger PDF</a>';
                }else{
                    content = '<h2>Aucun évènement selectionné</h2>';
                    var footer = '';
                }
                Swal.fire({
                    title: '<strong>Les transferts selectionnés</strong>',
                    icon: 'info',
                    width: 800,
                    html: content,
                    showCloseButton: true,
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText:
                    '<i class="fa fa-thumbs-up"></i> Super transférer tout ça !',
                    cancelButtonText:
                    '<i class="fa fa-thumbs-down"></i> Annuler',
                    footer: footer
                }).then((result) => {
                if (result.isConfirmed) {
                    transferPreplanning(array); // Call the transferPreplanning() function here
                }
            });
        }
    });
}
function transferPreplanning(preplannings) {
  var url = '{{ route('transfer.preplanning') }}';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      preplannings: preplannings,
      _token: '{{ csrf_token() }}'
    },
    success: function(data) {
      Swal.fire({
        title: 'Transfert réussi!',
        icon: 'success',
        text: 'Les préplannings sélectionnés ont été transférés avec succès.',
        showConfirmButton: false,
        timer: 2000
      });
      location.reload();
    },
    error: function(xhr, status, error) {
      var errorMessage = 'Une erreur s\'est produite lors du transfert des préplannings sélectionnés.';
      if (xhr.status === 422) {
        // If there is a validation error, show the error messages returned by the server
        var response = JSON.parse(xhr.responseText);
        errorMessage = '';
        for (var key in response.errors) {
          errorMessage += response.errors[key].join('<br>');
        }
      }
      Swal.fire({
        title: 'Erreur lors du transfert',
        icon: 'error',
        html: errorMessage,
        showConfirmButton: true
      });
    }
  });
}
    
    _loadAFOptions();
    function _loadAFOptions(){
        var select_id = 'AFOptions';
        var af_id_db = $('#af_id').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
                url: '/form/select/af',
                dataType: 'json',
                success: function(response) {
                    var array = response;
                    if (array != '') {
                        for (i in array) {
                            if(array[i].id == af_id_db )
                            {
                                $('#' + select_id).append("<option value='" + array[i].id + "' selected>" + array[i].name +
                                "</option>");
                            }
                            if(array[i].id != af_id_db )
                            {
                                $('#' + select_id).append("<option value='" + array[i].id + "' >" + array[i].name +
                                    "</option>");
                            }
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {
        });
    }
    
    function _updateIntervenant(){
        var pp_schedule_id = $('#pp_schedule_id').val();
        var interv_id = $('#SelectIntervenant').find(":selected").val();
        var start = $('#date_start').val() + " " + $('#heure-debut').val();
        var end = $('#date_start').val() + " " + $('#heure-fin').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url:'/form/update/interv/' + pp_schedule_id,
            data: {
                intervid:interv_id,
                start:start,
                end:end
            },
            dataType: 'JSON',
            success: function(result) {
                if (result.datas) {
                    // Build table
                    var tableHtml = '<table class="table table-striped table-bordered"><thead><tr><th>Titre de la Pré-planification</th><th>Titre de la Séance</th><th>Heure de début</th><th>Heure de fin</th></tr></thead><tbody>';
                    $.each(result.datas, function(i, data) {
                        var planningTitle = data.planning ? data.planning.title : '-';
                        tableHtml += '<tr><td>' + planningTitle + '</td><td>' + data.title + '</td><td>' + data.start_hour + '</td><td>' + data.end_hour + '</td></tr>';
                    });
                    tableHtml += '</tbody></table>';

                    // Show Swal alert with table
                    Swal.fire({
                        title: 'Intervenant disponible dans un autre planning',
                        html: tableHtml,
                        width: 800,
                        icon: 'error',
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                            container: 'my-swal-container-class'
                        },
                        // background: '#f44336' // Set background color to red
                    });
                }else{
                    // Show simple Swal alert
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Intervenant ajouté avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#interv_id').val(result.Contact_id);
                    _showIntervenants();
                    $("#SelectIntervenant").empty().append(new Option('--Sélectionner un Intervenant--',''));
                    _loadSelectIntervenant();
                }                
            }
        });
    }
    
    function _loadSelectIntervenant() {
        var select_id = 'SelectIntervenant';
        // var interv_id_db = $('#interv_id').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '/form/select/intervenants',
            dataType: 'json',
            success: function(response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        $('#' + select_id).append("<option value='" + array[i].id + "' >" + array[i].fullname +
                            "</option>");
                    }
                }
            },
            error: function(x, e) {}
        }).done(function() {
        });
    }
    function _updatePreplanning(planification_id){
        //var af_id = $('#AFOptions').find('option#id').val();
        var af_id =  $('#AFOptions').find(":selected").val();
        //console.log(af_id);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url:'/form/Preplanifications/update/' + planification_id,
            data: {
                afcibleid:af_id,
            },
            dataType: 'JSON',
            success: function(result) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Événement ajouté avec succès',
                    showConfirmButton: false,
                    timer: 3500
                });
                location.reload();
            }
        });
    }
    function _showIntervenants(){
        var showIntervenants = 'showIntervenants';
        var pp_schedule_id = $('#pp_schedule_id').val();
       
        $("#showIntervenants").empty();
        $.ajax({
            url: '/form/show/show-intervenants/'+ pp_schedule_id ,
            dataType: 'json',
            success: function(response) {
                //console.log(response);
                var array = response;
                if (array != '') {
                    for (i in array) {
                            $('#' + showIntervenants).append(
                                $('<tr>', {
                                            id:'tr'+ array[i].id,
                                        })
                            );
                            $('#tr'+ array[i].id).append("<td value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'> " + array[i].fullname + "</td>");
                            if(array[i].type == 'Sur facture' && array[i].price == null ){
                                $('#tr'+ array[i].id).append(
                                    $('<td>').append(
                                        $('<input>', {
                                            id:'price',
                                            type: 'number',
                                            class:'form-control mt-1',
                                            placeholder:'ajouté le prix ici',
                                            val: ''
                                        })
                                    )
                                );
                            }
                            if(array[i].type == 'Sur facture' && array[i].price != null ){
                                $('#tr'+ array[i].id).append("<td id='reloadprice_"+ array[i].id +"' value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'>" + array[i].price + "</td>");
                            }
                            // if(array[i].type == 'Sur contrat' && array[i].price == null ){
                            if(array[i].type == 'Sur contrat'){
                                $('#tr' + array[i].id).append(
                                    $('<td>', {
                                        id: 'td_select' + array[i].id,
                                        value: array[i].id
                                    })
                                    );
                                    $('#td_select' + array[i].id).append("<input id='interv_price_id_"+ array[i].id +"' type='hidden' value='" + (array[i].price ? array[i].price : 0) + "' />");
                                    $('#td_select' + array[i].id).append(
                                    $('<select>', {
                                        id: 'price_contrat_' + array[i].id,
                                        class: 'form-control'
                                    })
                                    );
                                    $('#price_contrat_' + array[i].id).append(new Option('-- Sélectionnez le tarif ici --', ''));

                                _loadSelectPriceIntervenant(array[i].id);
                            }
                            // if(array[i].type != 'Sur contrat' && array[i].type != 'Sur facture' && array[i].price == null){
                            //     $('#tr'+ array[i].id).append("<td value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'>"+ array[i].price + "</td>");
                            // }
                            if(array[i].type == 'Interne'){
                                $('#tr'+ array[i].id).append("<td value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'></td>");
                            }
                            $('#tr'+ array[i].id).append("<td value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'>"+ array[i].type+"</td>");
                            $('#tr'+ array[i].id).append("<td value='" + array[i].id + "' class='bg-light border border-info border-1 px-3'> " + array[i].action + "</td>");
                    }
                }
                    
            },
            error: function(x, e) {}
        }).done(function() {
        });
    }
    function _deleteIntervenant(id){
        //var pp_schedule_id = $('#pp_schedule_id').val();
        Swal.fire({
            title: 'Êtes-vous sûr de vouloir supprimer l\'intervenant ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Annuler',
            confirmButtonText: 'Oui, supprimez-le!'
            }).then((result) => {
            if (result.isConfirmed) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url:"/form/delete/interv/" + id,
                        type:"DELETE",
                        dataType:'json',
                        success:function(response)
                        {                
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Intervenant supprimé avec succès!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#interv_id').val(0);
                            _loadSelectIntervenant();
                            _showIntervenants();
                        },
                        error:function(error)
                        {
                            console.log(error)
                        },
                    });
            }
            })
        
    }
    function _reloadInputPrice(id) {
        var td = $('td#reloadprice_'+id);
        if(td.text()){
        console.log(td.text());
            var input = $('<input>', {
            id: 'price',
            val: parseFloat(td.text()),
            type: 'number',
            class: 'form-control mt-1',
            // placeholder: td.text()
            });
            td.html(input);
        }
    }
    function _editIntervenantFacture(id){
        var Surfacture = $('#reloadprice_' + id).text();
        price = $('#tr' + id + ' td:nth-child(2) input').val();
        if(Surfacture!= 0){
            Swal.fire({
                title: 'Pour Editer cliquez sur le bouton avec l\'icône du Crayon',
                icon: 'warning',
            })
            return;
        }
        if(!price){
            // Swal.fire({
            //     title: 'Veuillez remplir le champ',
            //     icon: 'warning',
            // })
            // return;
            price = null;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:"/form/edit/interv/" + id,
            type:"PATCH",
            data: {
                price:price,
            },
            success:function(response)
            {                
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Intervenant modifié avec succès!',
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#interv_price_id_'+id).val(response.id);
                _showIntervenants();
            },
            error:function(error)
            {
                console.log(error)
            },
        });
    }
    function _editIntervenantContrat(id){
        var selectValue = $('#td_select'+ id +' #price_contrat_'+ id +' option:selected').val();
        var selectprice = $('#td_select'+ id +' #price_contrat_'+ id +' option:selected').text();
        if(!selectValue){
            // Swal.fire({
            //     title: 'Veuillez remplir le champ',
            //     icon: 'warning',
            // })
            // return;
            selectprice = null;
        }
        var interv_price_id = $('#interv_price_id_'+id).val();
        if(selectprice == interv_price_id)
        {
            Swal.fire({
                title: 'le tarif existe déjà pour cet intervenant merci d\'en sélectionner un autre',
                icon: 'warning',
            })
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:"/form/edit/interv/" + id,
            type:"PATCH",
            data: {
                price:selectprice,
            },
            success:function(response)
            {                
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Intervenant modifié avec succès!',
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#interv_price_id_'+id).val(response.id);
                _showIntervenants();
            },
            error:function(error)
            {
                console.log(error)
            },
        });
    }
    function _loadSelectPriceIntervenant(id){
        var select_id = 'price_contrat_'+id;
        var interv_price_id = $('#interv_price_id_'+id).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '/form/select/intervenants_price',
            dataType: 'json',
            success: function(response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        if(array[i].price == interv_price_id)
                        {
                            $('#' + select_id).append("<option value='" + array[i].id + "' selected>" + array[i].price +
                            "</option>");
                        }
                        if(array[i].price != interv_price_id ){
                            $('#' + select_id).append("<option value='" + array[i].id + "' >" + array[i].price +
                            "</option>");
                        }
                        
                    }
                }
            },
            error: function(x, e) {}
        }).done(function() {
        });
    }
    $('#bookingModal').on('show.bs.modal', function () {
        var pp_schedule_id = $('#pp_schedule_id').val();
        //console.log(pp_schedule_id);
        $("#SelectIntervenant").empty().append(new Option('--Sélectionner un Intervenant--',''));
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'get',
            url:'/form/loadmodal/' + pp_schedule_id,
            data: {
                //afcibleid:af_id,
            },
            dataType: 'JSON',
            success: function(response) {
                if(response != ''){
                    $("#GroupeSelect").empty().append(new Option('--Sélectionner un Groupe--',''));
                    $("#RegroupementSelect").empty().append(new Option('--Sélectionner un Regroupement--',''));
                    $("#SelectIntervenant").empty().append(new Option('--Sélectionner un Intervenant--',''));
                    $('#group_id').val(response.group_id);
                    $('#Regroupement_id').val(response.Regroupement_id);
                    $('#interv_id').val(response.interv_id);
                    //console.log($('#interv_id').val());
                    _loadGroupeSelect();
                    _loadRegroupementSelect();
                    _loadSelectIntervenant();
                    _showIntervenants();
                }else{
                    _loadGroupeSelect();
                    _loadRegroupementSelect();
                    _loadSelectIntervenant();
                }
            }
        }); 
    });
    function _loadGroupeSelect() {
        var select_id = 'GroupeSelect';
        var group_id = $('#group_id').val();
        //console.log(group_id);
        var $af_id = $('#af_id').val();
        $.ajax({
            url: '/form/select/grp/'+ $af_id,
            dataType: 'json',
            success: function(response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        if(array[i].id == group_id )
                        {
                            $('#' + select_id).append("<option value='" + array[i].id + "' selected>" + array[i].name +
                            "</option>");
                        }
                        if(array[i].id != group_id ){
                            $('#' + select_id).append("<option value='" + array[i].id + "' >" + array[i].name + "</option>");
                        }
                    }
                }
            },
            error: function(x, e) {}
        }).done(function() {
        });
    }
    function _updateGroupeSelect(){
        var pp_schedule_id = $('#pp_schedule_id').val();
        var group_id = $('#GroupeSelect').find(":selected").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url:'/form/update/grp/' + pp_schedule_id,
            data: {
                groupid:group_id,
            },
            dataType: 'JSON',
            success: function(result) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Groupe ajouté avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                //location.reload();
            }
        });
    }
    //Regroupement select
    function _loadRegroupementSelect() {
        var Regroupementselect_id = 'RegroupementSelect';
        var Regroupement_id = $('#Regroupement_id').val();
        //console.log(group_id);
        var $af_id = $('#af_id').val();
        $.ajax({
            url: '/form/select/Regroupement/'+ $af_id,
            dataType: 'json',
            success: function(response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        if(array[i].id == Regroupement_id )
                        {
                            $('#' + Regroupementselect_id).append("<option value='" + array[i].id + "' selected>" + array[i].name +
                            "</option>");
                        }
                        if(array[i].id != Regroupement_id ){
                            $('#' + Regroupementselect_id).append("<option value='" + array[i].id + "' >" + array[i].name + "</option>");
                        }
                    }
                }
            },
            error: function(x, e) {}
        }).done(function() {
        });
    }
    function _updateRegroupementSelect(){
        var pp_schedule_id = $('#pp_schedule_id').val();
        var Regroupementselect_id = $('#RegroupementSelect').find(":selected").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url:'/form/update/Regroupement/' + pp_schedule_id,
            data: {
                Regroupementid:Regroupementselect_id,
            },
            dataType: 'JSON',
            success: function(result) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Regroupement ajouté avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                //location.reload();
            }
        });
    }

    $("#bookingModal").on("hidden.bs.modal", function () {
            $('#saveBtn').unbind();
            $('#group_id').val(0);
            $('#interv_id').val(0);
            // $('#GroupeSelect option').filter(':empty').remove();
    });
    //temporelle
    $('#structure_temporelle_tree').jstree({
        "core": {
            "multiple": false,
            "themes": {
                "responsive": true
            },
            //"check_callback" : false,
            'data': {
                'url': function(node) {
                    // return '/get/tree/time/structure/'+$("#product_id").val()+"/0";
                    return '/get/structure/'+$("#product_id").val();
                },
                'data': function(node) {
                    return {
                        'parent': node.id
                    };
                }
            },
        },
        "checkbox": {
            "three_state": false, // to avoid that fact that checking a node also check others
            //"whole_node" : false,  // to avoid checking the box just clicking the node
            //"tie_selection" : true // for checking without selecting and selecting without checking
        },
        "plugins": ["state"]
    });
</script>

<script> 
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var tooltip = null;
        var Draggable = FullCalendar.Draggable;
        var containerEl = document.getElementById('structure_temporelle_tree');
        new Draggable(containerEl, {
            itemSelector: '.jstree-leaf',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.innerText,
                        id: eventEl.getAttribute("id")
                    };
                }
        });
        var booking = @json($schedules);
        var latestEvent = booking[booking.length - 1].start;
        var initialView = latestEvent ? 'timeGridWeek' : 'dayGridMonth';
        
        //console.log(latestEvent);
        console.log(booking);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            // initialView: 'dayGridMonth',
            initialView: initialView,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'fr',
            timeZone: 'local',
            navLinks: true,
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar
            selectable: true,
            events: booking,
            eventContent: function(arg) {
                if(calendar.view.type == 'timeGridWeek' || calendar.view.type == 'timeGridDay'|| calendar.view.type == 'listWeek'){
                    let sequenceEl = document.createElement('div');
                    sequenceEl.innerHTML = "<span class='fw-bold text-dark'>Séquence</span> (" + arg.event.extendedProps.sequence_number + "/" + arg.event.extendedProps.sequence_total +")";

                    let defaultContent = "<span class='fw-bold'> " + arg.event.title.substring(0, 30) + "</span><br/> " +
                            "<span class='fw-bold text-dark'>Debut :</span>  " + moment(arg.event.start).format('HH:mm') +'<br/>'+ 
                            "<span class='fw-bold text-dark'>Fin :</span>  " + moment(arg.event.end).format('HH:mm');
                    let defaultEl = document.createElement('div');
                    defaultEl.innerHTML = defaultContent;

                    let arrayOfDomNodes = [sequenceEl, defaultEl];
                    return { domNodes: arrayOfDomNodes };
                }
                if(calendar.view.type === 'dayGridMonth')
                {
                    let sequenceEl = document.createElement('div');
                    let defaultContent = "<span class='fw-bold text-black'> " + arg.event.title.substring(0, 20) + "</span><br/> " +
                        "<span class='fw-bold text-black'>Debut :</span> <span class='text-black'> " + moment(arg.event.start).format('HH:mm') +"</span><br/>"+ 
                        "<span class='fw-bold text-black'>Fin :</span> <span class='text-black'> " + moment(arg.event.end).format('HH:mm') +"</span>";
                    let defaultEl = document.createElement('div');
                    defaultEl.innerHTML = defaultContent;

                    defaultEl.classList.add("fc-event");
                    defaultEl.style.backgroundColor = arg.event.backgroundColor;
                    defaultEl.style.width = "100%";

                    let arrayOfDomNodes = [sequenceEl, defaultEl];
                    return { domNodes: arrayOfDomNodes };
                }
            },
            eventDidMount: function(info) {
                if (calendar.view.type === 'dayGridMonth') {
                    var tooltipTitle = info.event.title + '<br />' +
                                    "Heure Debut : " + moment(info.event.start).format('HH:mm') + '<br />' +
                                    "Heure Fin : " + moment(info.event.end).format('HH:mm') + '<br />' +
                                    "Commentaire : ";
                    
                    if (info.event.extendedProps.remarks) {
                    tooltipTitle += info.event.extendedProps.remarks;
                    } else {
                    tooltipTitle += "pas de commentaire";
                    }
                    
                    $(info.el).tooltip({
                    title: tooltipTitle,
                    html: true,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                    });
                }
            },
            eventReceive: function(arg) { // called when a proper external event is dropped
                const eventData = {
                    title: arg.event.title,
                    start: moment(arg.event.start).hours(9).format(),
                    end: moment(arg.event.start).hours(12).format(),
                    pf_session: arg.event.id
                };
                var Pp_id = $('#Pp_id').val();
                var title = eventData.title;
                var date_start = eventData.start;
                var start_hour = eventData.start;
                var end_hour = eventData.end;
                var sequence_number = 1;
                var sequence_total = 1;
                var color = "#FFD966";
                var remarks = "";
                var Pf_session  = eventData.pf_session;
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url:'/calendar',
                    type:"POST",
                    dataType:'json',
                    data:{
                        Pp_id,
                        title,
                        date_start,
                        start_hour, 
                        end_hour,
                        sequence_number,
                        sequence_total,
                        color,
                        remarks,
                        Pf_session
                    },
                    success:function(response)
                    {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Événement ajouté avec succès',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        location.reload();
                    },
                    error: function (error) {
                        console.error(error);
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Une erreur est survenue',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                });
            },
            eventDrop: function(info) {
                    // duplicate
                    Swal.fire({
                            title: 'Voulez-vous Dupliquer ou Déplacer l\'événement?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Oui, Dupliquer',
                            confirmButtonColor: '#3085d6',
                            cancelButtonText: 'Oui, Déplacer',
                            cancelButtonColor: '#d33',
                        }).then((result) => {
                            if(result.isConfirmed){
                                var Pp_id = $('#Pp_id').val();
                                var title = info.event.title;
                                var date_start = moment(info.event.start).format();
                                var start_hour = moment(info.event.start).format();
                                var end_hour = moment(info.event.end).format();
                                var color = info.event.backgroundColor;
                                var sequence_total = info.event.extendedProps.sequence_total;
                                var sequence_number = info.event.extendedProps.sequence_number;
                                var remarks = info.event.extendedProps.remarks;
                                var Pf_session  = info.event.extendedProps.Pf_session;
                                var pp_schedule_id = info.event.id;
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                $.ajax({
                                    url:'/calendar/duplicate',
                                    type:"POST",
                                    dataType:'json',
                                    data:{
                                        Pp_id,
                                        title,
                                        date_start,
                                        start_hour, 
                                        end_hour,
                                        Pf_session,
                                        pp_schedule_id,
                                        color,
                                        remarks,
                                        sequence_number,
                                        sequence_total,
                                    },
                                    success:function(response)
                                    {
                                        if (response.datas) {
                                            // Build table
                                            var tableHtml = '<table class="table table-striped table-bordered"><thead><tr><th>Titre de la Pré-planification</th><th>Titre de la Séance</th><th>Heure de début</th><th>Heure de fin</th></tr></thead><tbody>';
                                            $.each(response.datas, function(i, data) {
                                                var planningTitle = data.planning ? data.planning.title : '-';
                                                tableHtml += '<tr><td>' + planningTitle + '</td><td>' + data.title + '</td><td>' + data.start_hour + '</td><td>' + data.end_hour + '</td></tr>';
                                            });
                                            tableHtml += '</tbody></table>';

                                            // Show Swal alert with table
                                            Swal.fire({
                                                title: 'Intervenant disponible dans un autre planning',
                                                html: tableHtml,
                                                width: 800,
                                                icon: 'error',
                                                showCloseButton: true,
                                                showConfirmButton: false,
                                                customClass: {
                                                    container: 'my-swal-container-class'
                                                },
                                                // background: '#f44336' // Set background color to red
                                                onClose: function() {
                                                    location.reload();
                                                }
                                            });
                                        }else{
                                            Swal.fire({
                                                position: 'center',
                                                icon: 'success',
                                                title: 'Événement ajouté avec succès',
                                                showConfirmButton: false,
                                                timer: 1500
                                            });
                                            location.reload();
                                        }
                                    },
                                    error:function(error)
                                    {
                                        if(error.responseJSON.errors) {
                                            $('#titleError').html(error.responseJSON.errors.title);
                                        }
                                    },
                                });
                            }else{
                                var id = info.event.id;
                                var date_start = moment(info.event.start).format();
                                var start_hour = moment(info.event.start).format();
                                var end_hour = moment(info.event.end).format();
                                
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                $.ajax({
                                url: '/calendar/dropUpdate/' + id,
                                type: "PATCH",
                                dataType: 'json',
                                data: {
                                    date_start: date_start,
                                    start_hour: start_hour,
                                    end_hour: end_hour
                                },
                                success: function(response) {
                                    if (response.datas) {
                                        // Build table
                                        var tableHtml = '<table class="table table-striped table-bordered"><thead><tr><th>Titre de la Pré-planification</th><th>Titre de la Séance</th><th>Heure de début</th><th>Heure de fin</th></tr></thead><tbody>';
                                        $.each(response.datas, function(i, data) {
                                            var planningTitle = data.planning ? data.planning.title : '-';
                                            tableHtml += '<tr><td>' + planningTitle + '</td><td>' + data.title + '</td><td>' + data.start_hour + '</td><td>' + data.end_hour + '</td></tr>';
                                        });
                                        tableHtml += '</tbody></table>';

                                        // Show Swal alert with table
                                        Swal.fire({
                                            title: 'Intervenant disponible dans un autre planning',
                                            html: tableHtml,
                                            width: 800,
                                            icon: 'error',
                                            showCloseButton: true,
                                            showConfirmButton: false,
                                            customClass: {
                                                container: 'my-swal-container-class'
                                            },
                                            // background: '#f44336' // Set background color to red
                                            onClose: function() {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            position: 'center',
                                            icon: 'success',
                                            title: 'Événement Modifié avec succès',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        location.reload();
                                    }
                                }
                            });

                            }
                        });
            },
            eventClick: function(info){
                // console.log(info.event.startEditable)
                if (info.event.startEditable){
                    $('#bookingModal').on('show.bs.modal', function(event) {
                    $(this).find('.modal-content *').css('pointer-events', 'auto');
                    $(this).find('.modal-content').css('background-color', '#fff');
                    $(this).find('.modal-body').css('pointer-events', 'none');
                    $(this).find('.modal-body').css({
                            'background-color': '#fff',
                            'pointer-events': 'auto'
                        });
                        $(this).find('.modal-footer').show(); 
                    });
                    $('#saveBtn').text('Mettre à jour');
                    
                    $('#pp_schedule_id').val(info.event.id);
                    $('.select2').each(function() { 
                        $(this).select2({ dropdownParent: $(this).parent()});
                    });
                    var id = info.event.id;
                    var formatted_date = info.event.start.getFullYear() + "-" + (info.event.start.getMonth() + 1) + "-" + info.event.start.getDate();
                    var formatted_start_hour = info.event.start.getHours() + ":" + info.event.start.getMinutes();
                    var formatted_end_hour = info.event.end.getHours() + ":" + info.event.end.getMinutes();
                    $('#bookingModal').modal('toggle');
                        $('#title').val(info.event.title);
                        $('#sequence_number').val(info.event.extendedProps.sequence_number);
                        $('#sequence_total').val(info.event.extendedProps.sequence_total);
                        $('#remarks').val(info.event.extendedProps.remarks);
                        $('#date_start').datepicker("update", new Date(formatted_date));
                        $('#heure-debut').timepicker("setTime",formatted_start_hour);
                        $('#heure-fin').timepicker("setTime", formatted_end_hour);
                    $('#deleteBtn').click(function() {
                        $('#bookingModal').modal('hide');
                        Swal.fire({
                            title: 'Voulez-vous vraiment supprimer l’événement?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Oui, supprimez-le !',
                            confirmButtonColor: '#3085d6',
                            cancelButtonText: 'Annuler',
                            cancelButtonColor: '#d33',
                        }).then((result) => {
                            if(result.isConfirmed){
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                $.ajax({
                                    url:'/calendar/destroy/'+ id,
                                    type:"DELETE",
                                    dataType:'json',
                                    success:function(response)
                                    {
                                        Swal.fire({
                                            position: 'center',
                                            icon: 'success',
                                            title: 'Événement supprimé!',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        location.reload();
                                    },
                                    error:function(error)
                                    {
                                        console.log(error)
                                    },
                                });
                            }
                        });
                    });
                    $('#saveBtn').click(function(){
                        var id = info.event.id;
                        var title = $('#title').val();
                        var sequence_number = $('#sequence_number').val();
                        var sequence_total = $('#sequence_total').val();
                        var remarks = $('#remarks').val();
                        var date_start = $('#date_start').val();
                        var heure_debut = date_start + " " + $('#heure-debut').val();
                        var heure_fin = date_start + " " + $('#heure-fin').val();
                    
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url:'/calendar/update/'+ id,
                            type:"PATCH",
                            dataType:'json',
                            data:{
                                title,
                                date_start, 
                                heure_debut,
                                heure_fin,
                                sequence_number,
                                sequence_total,
                                remarks
                            },
                            success:function(response)
                            {
                            if (response.datas) {
                                    // Build table
                                    var tableHtml = '<table class="table table-striped table-bordered"><thead><tr><th>Titre de la Pré-planification</th><th>Titre de la Séance</th><th>Heure de début</th><th>Heure de fin</th></tr></thead><tbody>';
                                    $.each(response.datas, function(i, data) {
                                        var planningTitle = data.planning ? data.planning.title : '-';
                                        tableHtml += '<tr><td>' + planningTitle + '</td><td>' + data.title + '</td><td>' + data.start_hour + '</td><td>' + data.end_hour + '</td></tr>';
                                    });
                                    tableHtml += '</tbody></table>';

                                    // Show Swal alert with table
                                    Swal.fire({
                                        title: 'Intervenant disponible dans un autre planning',
                                        html: tableHtml,
                                        width: 800,
                                        icon: 'error',
                                        showCloseButton: true,
                                        showConfirmButton: false,
                                        customClass: {
                                            container: 'my-swal-container-class'
                                        },
                                        // background: '#f44336' // Set background color to red
                                        onClose: function() {
                                            location.reload();
                                        }
                                    });
                                }else{
                                    Swal.fire({
                                    position: 'center',
                                    icon: 'success',
                                    title: 'Événement modifié',
                                    showConfirmButton: false,
                                    timer: 1500
                                    });
                                    location.reload();
                                }
                            }
                            });

                        });
                }else{
                    $('#exampleModalLabel').text('Cette session est déjà transférée');
                    $('#bookingModal').on('show.bs.modal', function(event) {
                        $(this).find('.modal-content *').css('pointer-events', 'none');
                        $(this).find('.modal-content').css('background-color', '#2986CC');
                        $(this).find('.modal-body').css('pointer-events', 'auto');
                        $(this).find('.modal-body').css({
                            'background-color': '#2986CC',
                            'pointer-events': 'auto'
                        });
                        $(this).find('.modal-footer').hide(); 
                    });
                    $('#pp_schedule_id').val(info.event.id);
                    $('.select2').each(function() { 
                        $(this).select2({ dropdownParent: $(this).parent()});
                    });
                    var id = info.event.id;
                    var formatted_date = info.event.start.getFullYear() + "-" + (info.event.start.getMonth() + 1) + "-" + info.event.start.getDate();
                    var formatted_start_hour = info.event.start.getHours() + ":" + info.event.start.getMinutes();
                    var formatted_end_hour = info.event.end.getHours() + ":" + info.event.end.getMinutes();
                    $('#bookingModal').modal('toggle');
                        $('#title').val(info.event.title);
                        $('#sequence_number').val(info.event.extendedProps.sequence_number);
                        $('#sequence_total').val(info.event.extendedProps.sequence_total);
                        $('#remarks').val(info.event.extendedProps.remarks);
                        $('#date_start').datepicker("update", new Date(formatted_date));
                        $('#heure-debut').timepicker("setTime",formatted_start_hour);
                        $('#heure-fin').timepicker("setTime", formatted_end_hour);
                }   
            },
        });
        if (latestEvent) {
            calendar.changeView('timeGridWeek', latestEvent);
        }
        calendar.render();
        $("#bookingModal").on("hidden.bs.modal", function () {
            $('#saveBtn').unbind();
        });
        calendar.setOption('locale', 'fr');
    });
</script>

@endsection