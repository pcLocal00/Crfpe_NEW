<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" <?php echo e(Metronic::printAttrs('html')); ?> <?php echo e(Metronic::printClasses('html')); ?>>
    <head>
        <meta charset="utf-8"/>

        
        <title><?php echo e(config('app.name')); ?> | <?php echo $__env->yieldContent('title', $page_title ?? ''); ?></title>

        
        <meta name="description" content="<?php echo $__env->yieldContent('page_description', $page_description ?? ''); ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        
        <link rel="shortcut icon" href="<?php echo e(asset('media/logo/favicon.ico')); ?>" />

        
        <?php echo e(Metronic::getGoogleFontsInclude()); ?>


        <!--begin::Page Custom Styles(used by this page)-->
		<link href="<?php echo e(asset('css/pages/login/login-1.css')); ?>" rel="stylesheet" type="text/css" />
		<!--end::Page Custom Styles-->

        
        <?php $__currentLoopData = config('layout.resources.css'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <link href="<?php echo e(config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style)); ?>" rel="stylesheet" type="text/css"/>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php $__currentLoopData = Metronic::initThemes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <link href="<?php echo e(config('layout.self.rtl') ? asset(Metronic::rtlCssPath($theme)) : asset($theme)); ?>" rel="stylesheet" type="text/css"/>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php echo $__env->yieldContent('styles'); ?>
    </head>

    <body <?php echo e(Metronic::printAttrs('body')); ?> <?php echo e(Metronic::printClasses('body')); ?>>
        
        <?php echo $__env->yieldContent('content'); ?>

        
        <script>
            var KTAppSettings = <?php echo json_encode(config('layout.js'), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES); ?>;
        </script>

        
        <?php $__currentLoopData = config('layout.resources.js'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <script src="<?php echo e(asset($script)); ?>" type="text/javascript"></script>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php echo $__env->yieldContent('scripts'); ?>

    </body>
</html>

<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/layout/login_layout.blade.php ENDPATH**/ ?>