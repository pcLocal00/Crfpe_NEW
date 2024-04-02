

<div class="subheader py-2 <?php echo e(Metronic::printClasses('subheader', false)); ?>" id="kt_subheader">
    <div class="<?php echo e(Metronic::printClasses('subheader-container', false)); ?> d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">

		
        <div class="d-flex align-items-center flex-wrap mr-1">

			
            <h5 class="text-dark font-weight-bold my-2 mr-5">
                <?php echo e(@$page_title); ?>


                <?php if(isset($page_description) && config('layout.subheader.displayDesc')): ?>
                    <small><?php echo e(@$page_description); ?></small>
                <?php endif; ?>
            </h5>

            <?php if(!empty($page_breadcrumbs)): ?>
				
                <div class="subheader-separator subheader-separator-ver my-2 mr-4 d-none"></div>

				
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2">
                    <li class="breadcrumb-item"><a href="#"><i class="flaticon2-shelter text-muted icon-1x"></i></a></li>
                    <?php $__currentLoopData = $page_breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<li class="breadcrumb-item">
                        	<a href="<?php echo e(url($item['page'])); ?>" class="text-muted">
                            	<?php echo e($item['title']); ?>

                        	</a>
						</li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>

		
        <div class="d-flex align-items-center">
            <?php if (! empty(trim($__env->yieldContent('page_toolbar')))): ?>
                <?php $__env->startSection('page_toolbar'); ?>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/layout/partials/subheader/_subheader-v1.blade.php ENDPATH**/ ?>