<?php
    use Carbon\Carbon;
    $dateNow = Carbon::now()->format('Y-m-d');
?>

<div class="modal-header">
    <h5 class="modal-title" id="modal_show_facture_title"><i class="flaticon-edit"></i> Dépot de facture</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="acceptFacture" class="form" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php if(isset($devis)): ?>
        <div class="modal-body" id="modal_show_facture_body">
            <div data-scroll="true" data-height="550">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="max_nb_trainees">Date du facture : </label>
                            <label for="max_nb_trainees"> <?php echo e($devis->created_at->format('Y-m-d')); ?> </label>
                            <input class="form-control " type="text" name="af_id" id="af_id" value="<?php echo e($devis->action->id); ?>" hidden />
                            <input class="form-control " type="text" name="devis_id" id="devis_id" value="<?php echo e($devis->id); ?>" hidden />
                            <input class="form-control " type="text" name="member_id" id="member_id" value="<?php echo e($member_id); ?>" hidden />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <a href="<?php echo e(asset('uploads/document/' . $devis->path)); ?>" target="_blank">
                            <?php echo e($devis->path); ?> <i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="modal-footer">
        <button type="button" id="refuserButton" class="btn btn-sm btn-light-danger"onclick="_show_motif(<?php echo e($devis->id); ?>,<?php echo e($member_id); ?>)">
            <i class="fa fa-times"></i> Refuser
        </button>
        <button type="submit" id="accepterButton" class="btn btn-sm btn-primary">
            <i class="fa fa-check"></i> Accepter <span id="BTN_SAVE_POINTAGE"></span>
        </button>
    </div>
</form>

<script>
    function _show_motif(devis_id, member_id) {
        document.getElementById('accepterButton').disabled = true;
        var modal_id = 'modal_show_motif';
        var modal_content_id = 'modal_show_motif_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/view/show/motif/devis/' + devis_id + '/' + member_id +'/3',
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }

    function _show_documents(id,action_id,member_id) {
        var modal_id = 'modal_download_document';
        var modal_content_id = 'modal_download_document_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/view/show/download/document/'+id +'/'+ action_id +'/'+ member_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }

    $(document).ready(function() {
        $('#acceptFacture').submit(function(e) {
            e.preventDefault();
            document.getElementById('refuserButton').disabled = true;
            var action_id = $('#af_id').val();
            var member_id = $('#member_id').val();
            _show_documents(2,action_id,member_id)
        });
    });
</script>
<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/formation/devis/three.blade.php ENDPATH**/ ?>