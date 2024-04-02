<?php
	$direction = config('layout.extras.user.offcanvas.direction', 'right');
?>
 
<div id="kt_quick_user" class="offcanvas offcanvas-<?php echo e($direction); ?> p-10">
	
	<div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
		<h3 class="font-weight-bold m-0">
			Mon Profil
		</h3>
		<a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
			<i class="ki ki-close icon-xs text-muted"></i>
		</a>
	</div>

	
    <div class="offcanvas-content pr-5 mr-n5">
		
        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-100 mr-5">
                <div class="symbol-label" style="background-image:url('<?php echo e(asset('media/users/blank.png')); ?>')"></div>
				<i class="symbol-badge bg-success"></i>
            </div>
            <div class="d-flex flex-column">
                <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
					<?php echo e(Auth::user()->name); ?>

				</a>
                <div class="text-muted mt-1">
                    Application Developer
                </div>
                <div class="navi mt-2">
                    <a href="#" class="navi-item">
                        <span class="navi-link p-0 pb-2">
                            <span class="navi-icon mr-1">
								<?php echo e(Metronic::getSVG("media/svg/icons/Communication/Mail-notification.svg", "svg-icon-lg svg-icon-primary")); ?>

							</span>
                            <span class="navi-text text-muted text-hover-primary"><?php echo e(Auth::user()->email); ?></span>
                        </span>
                    </a>

					<!-- Authentication -->
					<form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
						<a href="route('logout')" class="btn btn-sm btn-light-primary font-weight-bolder py-2 px-5" onclick="event.preventDefault();this.closest('form').submit();">Déconnexion</a>
                    </form>
                </div>
            </div>
        </div>

        
		<div class="separator separator-dashed mt-8 mb-5"></div>

		
        
		<div class="navi navi-spacer-x-0 p-0">
		    
            <form  action="<?php echo e(url('/getnewpassword')); ?>">
                <!-- <?php echo csrf_field(); ?> -->
                <?php echo method_field('put'); ?>
                <?php echo csrf_field(); ?>
                <a href="<?php echo e(url('/getnewpassword')); ?>" class="navi-item">
                    <div class="navi-link">
                        <div class="symbol symbol-40 bg-light mr-3">
                            <div class="symbol-label">
                                <?php echo e(Metronic::getSVG("media/svg/icons/General/Notification2.svg", "svg-icon-md svg-icon-success")); ?>

                            </div>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">
                                Mon profile
                            </div>
                            <div class="text-muted">
                                Changement de mot de passe
                            </div>
                        </div>
                    </div>
                </a>
            </form>
        </div>
    </div>
</div>

<?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/layout/partials/extras/offcanvas/_quick-user.blade.php ENDPATH**/ ?>