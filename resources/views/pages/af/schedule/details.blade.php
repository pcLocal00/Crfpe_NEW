@php
$modal_title=$firstname.' '.$lastname;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_schedule_details_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_schedule_details_body">
    <div data-scroll="true" data-height="600">
        <div class="row">
            <div class="col-lg-12">
                <p class="text-primary">{{$nameEntity . $refEntity}} - {{$entityType}}</p>
                {!! $spanPlanif !!}
            </div>
        </div>
    </div>
</div>

<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
</script>