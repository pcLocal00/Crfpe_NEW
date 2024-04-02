@php
$modal_title='Contract';
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
                {!! $html !!}
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
function _formPointage(schedulecontact_id) {
            var modal_id = 'modal_form_pointage';
            var modal_content_id = 'modal_form_pointage_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/pointage/' + schedulecontact_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                }
            });
}
</script>