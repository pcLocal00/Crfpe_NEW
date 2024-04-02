<?php if($sheet): ?>

<div class="card card-custom card-fit card-border">
    <div class="card-body p-4">
        <div class="col-md-12">
            <p>Crée le : <span
                    class="label label-outline-warning label-pill label-inline"><?php echo e($sheet->created_at->format('d/m/Y H:i')); ?></span>
                - Modifiée le : <span
                    class="label label-outline-warning label-pill label-inline"><?php echo e($sheet->updated_at->format('d/m/Y H:i')); ?></span>
                - Etat : <span
                    class="label label-outline-warning label-pill label-inline"><?php echo e($sheet->state->name); ?></span>
            </p>
        </div>
        <div class="col-md-12">
            <p><!-- Fiche n° : <span class="label label-outline-warning"><?php echo e($sheet->id); ?></span> - --> Code : <span
                    class="label label-outline-warning label-pill label-inline"><?php echo e($sheet->ft_code); ?></span> - Version :
                <span class="label label-outline-warning"><?php echo e($sheet->version); ?></span>
            </p>
        </div>
        <div class="col-md-12">
        <p>Description : </p>
        <?php echo $sheet->description; ?>

        </div>
    </div>
</div>

<!--begin::nav-->
<div class="card card-custom card-fit card-border mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                <ul class="nav flex-column nav-pills">
                    <?php if($sheetParams): ?>
                    <?php $__currentLoopData = $sheetParams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php if($key==0): ?>active <?php endif; ?>" id="sheetparam-tab-<?php echo e($sp->id); ?>"
                            data-toggle="tab" href="#sheetparam-<?php echo e($sp->id); ?>">
                            <span class="nav-icon">
                                <i class="flaticon2-chat-1"></i>
                            </span>
                            <span class="nav-text"><?php echo e($sp->title); ?></span>
                        </a>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-8">
                <div class="tab-content">

                    <?php if($sheetParams): ?>
                    <?php $__currentLoopData = $sheetParams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-pane fade <?php if($key==0): ?>show active <?php endif; ?>" id="sheetparam-<?php echo e($sp->id); ?>"
                        role="tabpanel" aria-labelledby="sheetparam-tab-<?php echo e($sp->id); ?>">
                        <!--begin::Card-->
                        <div class="card card-custom card-fit card-border">
                            <div class="card-body">
                                <?php echo $sp->content; ?>

                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Card-->
<!--end::nav-->
<?php else: ?>
<div class="alert alert-custom alert-outline-danger fade show mb-5" role="alert">
    <div class="alert-icon">
        <i class="flaticon-warning"></i>
    </div>
    <div class="alert-text">Aucune fiche technique n'est définie pour cet action de formation</div>
</div>
<?php endif; ?><?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/af/sheet/view.blade.php ENDPATH**/ ?>