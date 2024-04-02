<div class="modal-header">
    <h5 class="modal-title" id="modal_logs_title"><i class="flaticon-edit"></i> {{ $title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_logs_body">
    <div data-scroll="true" data-height="400">
        @if($logs)
            @foreach($logs as $log)
                <p class="text-danger">- N° {{$log->id}} - {{$log->created_at->format('d/m/Y H:i')}} - {{$log->log_desc}}</p>
            @endforeach
        @else
            <p>Pas d'erreur lors de l'import du fichier</p>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Fermer</button>
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