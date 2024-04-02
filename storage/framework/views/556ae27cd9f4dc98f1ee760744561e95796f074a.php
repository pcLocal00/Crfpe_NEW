


<style>
	.button {
	text-decoration: none;
	font-size: .875rem;
	text-transform: uppercase;
	display: inline-block;
	border-radius: 1.5rem;
	background-color: #fff;
	font-weight: 900;
	}
	
	.popup {
	display: flex;
	align-items: center;
	justify-content: center;
	position: fixed;
	width: 100vw;
	height: 100vh;
	bottom: 0;
	right: 0;
	background-color: rgba(0, 0, 0, 0.8);
	z-index: 2;
	visibility: hidden;
	opacity: 0;
	overflow: hiden;
	transition: .64s ease-in-out;
	}
	.popup-inner {
	position: relative;
	bottom: -100vw;
	right: -100vh;
	/* display: flex; */
	align-items: center;
	max-width: 800px;
	max-height: 600px;
	width: 40%;
	height: 40%;
	background-color: #fff;
	transform: rotate(32deg);
	transition: .64s ease-in-out;
	border-radius: 8px;
	}
	.popuptext {
	display: flex;
	flex-direction: column;
	justify-content: center;
	height: 100%;
	padding: 4rem;
	}
	.popuptext h1 {
	font-size: 2rem;
	font-weight: 600;
	margin-bottom: 2rem;
	text-transform: uppercase;
	color: #0A0A0A;
	}
	.popuptext p {
	font-size: .875rem;
	color: #686868;
	line-height: 1.5;
	}
	
	.popuptext a {
	text-decoration: none;
	color:  #47BDFF;
	}
	.popup:target {
	visibility: visible;
	opacity: 1;
	}
	.popup:target .popup-inner {
	bottom: 0;
	right: 0;
	transform: rotate(0);
	}
	.closepopup {
	position: absolute;
	right: -1rem;
	top: -1rem;
	width: 3rem;
	height: 3rem;
	font-size: .875rem;
	font-weight: 300;
	border-radius: 100%;
	background-color: #47BDFF;
	z-index: 4;
	color: #fff;
	line-height: 3rem;
	text-align: center;
	cursor: pointer;
	text-decoration: none;
	}

	.list-btn{
		display: block;
		text-align: center;
		padding: 20px;
	}
</style>


<?php $__env->startSection('content'); ?>
<!--begin::Main-->
<div class="d-flex flex-column flex-root">
			<!--begin::Login-->
			<div class="login login-1 login-signin-on d-flex flex-column flex-lg-row flex-column-fluid bg-white" id="kt_login">
				<!--begin::Aside-->
				<div class="login-aside d-flex flex-column flex-row-auto" style="background-color: #e5edf3;">
					<!--begin::Aside Top-->
					<div class="d-flex flex-column-auto flex-column pt-lg-40 pt-15">
						<!--begin::Aside header-->
						<a href="#" class="text-center mb-10">
							<img src="media/logo/logo.png" class="max-h-100px" alt="" />
						</a>
						<!--end::Aside header-->
						<!--begin::Aside title-->
						<h3 class="font-weight-bolder text-center font-size-h4 font-size-h1-lg" style="color: #004d8e;">Centre Régional de Formation
						<br />des Professionnels de l'Enfance</h3>
						<!--end::Aside title-->
					</div>
					<!--end::Aside Top-->
					<!--begin::Aside Bottom-->
					<!-- <div class="aside-img d-flex flex-row-fluid bgi-no-repeat bgi-position-y-bottom bgi-position-x-center" style="background-image: url(media/svg/illustrations/login-visual-1.svg)"></div> -->
					<!--end::Aside Bottom-->
				</div>
				<!--begin::Aside-->
				<!--begin::Content-->
				<div class="login-content flex-row-fluid d-flex flex-column justify-content-center position-relative overflow-hidden p-7 mx-auto">
					<!--begin::Content body-->
					<div class="d-flex flex-column-fluid flex-center">
						<!--begin::Signin-->
						<div class="login-form login-signin">
							<!--begin::Form-->
							<!-- <form method="POST" action="<?php echo e(route('login')); ?>">
							<?php echo csrf_field(); ?> -->
							<!-- <form action="#" @submit.prevent="handleLogin"> -->
							<form class="form" novalidate="novalidate" id="kt_login_signin_form">
								<?php echo csrf_field(); ?>
								<!--begin::Title-->
								<div class="pb-13 pt-lg-0 pt-5">

									<?php if(env('SERVER_TYPE')==1): ?>
									<h1 class="text-warning">Serveur de pré production</h1>
									<?php endif; ?>
									<?php if(env('SERVER_TYPE')==2): ?>
									<h1 class="text-primary">Environnement de formation pour CRFPE</h1>
									<?php endif; ?>

									<h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Bienvenue au CRFPE</h3>
									<!-- <span class="text-muted font-weight-bold font-size-h4">New Here?
									<a href="javascript:;" id="kt_login_signup" class="text-primary font-weight-bolder">Create an Account</a></span> -->
									<span class="text-muted font-weight-bold font-size-h4">Plateforme de formation SOLARIS</span>
								</div>
								<!--begin::Title-->
								<!--begin::Form group-->
								<div class="form-group">
									<label class="font-size-h6 font-weight-bolder text-dark">Login</label>
									<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg" type="email" name="email" id="email" v-model="form.email" required/>

									<!-- <div class="fv-plugins-message-container" v-if="errors.email"><div class="fv-help-block">{{ errors.email[0] }}</div></div> -->
								
								</div>
								<!--end::Form group-->
								<!--begin::Form group-->
								<div class="form-group">
									<div class="d-flex justify-content-between mt-n5">
										<label class="font-size-h6 font-weight-bolder text-dark pt-5">Mot de passe</label>
										<!-- <?php if(Route::has('password.request')): ?>
										<a href="<?php echo e(route('password.request')); ?>" class="text-primary font-size-h6 font-weight-bolder text-hover-primary pt-5" id="kt_login_forgot">Mot de passe oublié ?</a>
										<?php endif; ?> -->
									</div>
									<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg" type="password" name="password" id="password" required autocomplete="current-password" v-model="form.password" />
									<!-- <div class="fv-plugins-message-container" v-if="errors.password"><div class="fv-help-block">{{ errors.password[0] }}</div></div> -->
								</div>
								<!--end::Form group-->


								<!-- Remember Me -->
								<div class="form-group">
									<div class="checkbox-inline">
										<label class="checkbox">
												<input type="checkbox" value="1" name="remember"><span></span>Se souvenir de moi
										</label>

										<a class="button" href="#popup">Mot de passe oublié</a>	

										<div class="container">
											<div class="popup" id="popup">
												<div class="popup-inner">
													<div class="popuptext">
														<h1>Trouvez votre compte</h1>
														<p>Veuillez entrer votre adresse e-mail pour rechercher votre compte.</p>
														<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg" type="email" name="recapemail" id="recapemail" >
														<div class="list-btn">
															<button type="submit" id="connect" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3">Se connecter</button>
															<button type="submit" id="annulate" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3" style="background:#8080806e; border:1px solid #8080801c;">Annuler</button>
														</div>
													</div>
													<a class="closepopup" id="closepopup" href="#">X</a>
												</div>
											</div>
										</div>	

									</div>
								</div>



								<!--begin::Action-->
								<div class="pb-lg-0 pb-5">
									<button type="submit" id="kt_login_signin_submit" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3">Se connecter <span id="ID_LOGIN_LOADER"></span></button>
								</div>
								<!--end::Action-->
							</form>
							<!--end::Form-->
						</div>
						<!--end::Signin-->
						<!--begin::Signup-->
						
						<!--end::Signup-->
						<!--begin::Forgot-->
						
						<!--end::Forgot-->
					</div>
					<!--end::Content body-->
					<!--begin::Content footer-->
					<div class="d-flex justify-content-lg-start justify-content-center align-items-end py-7 py-lg-0">
						<div class="text-dark-50 font-size-lg font-weight-bolder mr-10">
							<span class="mr-1"><?php echo e(date('Y')); ?>© Plateforme de formation SOLARIS</span>
						</div>
						<!-- <a href="#" class="text-primary font-weight-bolder font-size-lg">Terms</a>
						<a href="#" class="text-primary ml-5 font-weight-bolder font-size-lg">Plans</a>
						<a href="#" class="text-primary ml-5 font-weight-bolder font-size-lg">Contact Us</a> -->
					</div>
					<!--end::Content footer-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Login-->
		</div>
		<!--end::Main-->
<?php $__env->stopSection(); ?>


<?php $__env->startSection('scripts'); ?>
	<script src="<?php echo e(asset('custom/js/general.js?v=1')); ?>"></script>
	<script src="<?php echo e(asset('custom/js/login.js?v=1')); ?>"></script>

	<script>
		$("#connect").click(function() {
			var email = document.querySelector('#recapemail').value;
			var formData=[];
			if (email){
				formData = formData.concat([{
					name: "email",
					value: email
				}]);
			}
			
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				type: 'POST',
				url: '/resetpassword',
				data: formData,
				dataType: 'JSON',
				success: function(result) {
					if (result.success) {
						Swal.fire({
							icon: 'success',
							title: 'succès',
							text: result.message,
						})
						_reload_dt_tasks_stats();
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: result.message,
						})
					}
				}
			});
		});

		$("#annulate").click(function() {
			document.getElementById("closepopup").click();
		});
	</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.login_layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/auth/login.blade.php ENDPATH**/ ?>