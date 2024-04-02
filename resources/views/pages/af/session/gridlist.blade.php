@if(count($sessions)>0)
@foreach($sessions as $session)
<!--begin::Col-->
<div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
    <x-sessioncard :session="$session"/>
</div>
<!--end::Col-->
@endforeach
@else
<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="alert alert-custom alert-outline-danger fade show mb-5" role="alert">
                <div class="alert-icon">
                    <i class="flaticon-warning"></i>
                </div>
                <div class="alert-text">Aucune session pour cet action de formation <button onclick="_formSession(0)"
                        class="btn btn-sm btn-icon btn-light-primary"><i class="flaticon2-add-1"></i></button></div>
            </div>
        </div>
    </div>
</div>
@endif