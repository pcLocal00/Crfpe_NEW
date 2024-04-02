{{-- Extends layout --}}
@extends('layout.login_layout')

{{-- Content --}}
@section('content')
<!--begin::Main-->
<div class="d-flex flex-column flex-root">
	<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
		<div class="d-flex flex-column-fluid">
			<!--begin::Container-->
			<div class="container">
				<!--begin::Card-->
				<div class="card card-custom" style="margin-top: 170px;">
					<!--begin::Card header-->
					<div class="card-header card-header-tabs-line nav-tabs-line-3x">
						<!--begin::Toolbar-->
						<div class="card-toolbar">
							<ul class="nav nav-tabs nav-bold nav-tabs-line nav-tabs-line-3x">
								<!--end::Item-->
								<!--begin::Item-->
								<li class="nav-item mr-3">
									<a class="nav-link active" data-toggle="tab" href="#kt_user_edit_tab_3">
										<span class="nav-icon">
											<span class="svg-icon">
												<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Communication/Shield-user.svg-->
												<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
													<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
														<rect x="0" y="0" width="24" height="24"></rect>
														<path d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z" fill="#000000" opacity="0.3"></path>
														<path d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z" fill="#000000" opacity="0.3"></path>
														<path d="M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 C14.5228466,17 11.463736,17 7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z" fill="#000000" opacity="0.3"></path>
													</g>
												</svg>
												<!--end::Svg Icon-->
											</span>
										</span>
										<span class="nav-text font-size-lg">Changement de mot de pass</span>
									</a>
								</li>
								<!--end::Item-->
							</ul>
						</div>
						<!--end::Toolbar-->
					</div>
					<!--end::Card header-->
					<!--begin::Card body-->
					<div class="card-body">
						<form class="form" id="changepass">
						@method('post')
						@csrf
							<div class="tab-content">
								<div class="tab-pane px-7 active" id="kt_user_edit_tab_3" role="tabpanel">
									<!--begin::Body-->
									<div class="card-body">
										<!--begin::Row-->
										<div class="row">
											<div class="col-xl-2"></div>
											<div class="col-xl-7">

											<div class="row mb-5" >
												<label class="col-3"></label>
												<div class="col-9" id="popup" style="display: none;">
													<div class="alert alert-custom alert-light-danger fade show py-4" role="alert">
														<div class="alert-icon">
															<i class="flaticon-warning"></i>
														</div>
														<div class="alert-text font-weight-bold">Merci de bien vouloir vérifier vos cordonnées.</div>
														<div class="alert-close">
															<button type="button" class="close" data-dismiss="alert" aria-label="Close">
																<span aria-hidden="true">
																	<i class="la la-close"></i>
																</span>
															</button>
														</div>
													</div>
												</div>
												<div class="col-9" id="popup1" style="display: none;">
													<div class="alert alert-custom alert-light-danger fade show py-4" role="alert" style="background-color: #B3E3CD;">
														<div class="alert-text font-weight-bold" style="color: #14603C;">Votre mot de passe a été modifié avec avec succès!</div>
														<div class="alert-close">
															<button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #14603C !important;">
																<span aria-hidden="true">
																	<i class="la la-close"></i>
																</span>
															</button>
														</div>
													</div>
												</div>
											</div>

												<!--end::Row-->
												<!--begin::Row-->
												<div class="row">
													<label class="col-4"></label>
													<div class="col-8">
														<!-- <h6 class="text-dark font-weight-bold mb-10">Modifier ou récupérer votre mot de passe :</h6> -->
													</div>
												</div>
												<div class="form-group row">
													<label class="col-form-label col-4 text-lg-right text-left">Email</label>
													<div class="col-8">
														<input class="form-control form-control-lg form-control-solid" type="text" id="email" value="{{ auth()->user()->email }}">
													</div>
												</div>
												<!--end::Row-->
												<!--begin::Group-->
												<div class="form-group row">
													<label class="col-form-label col-4 text-lg-right text-left">Mot de passe actuel</label>
													<div class="col-8">
														<input class="form-control form-control-lg form-control-solid mb-1" type="text" id="oldpass">
													</div>
												</div>
												<!--end::Group-->
												<!--begin::Group-->
												<div class="form-group row">
													<label class="col-form-label col-4 text-lg-right text-left">Nouveau mot de passe</label>
													<div class="col-8">
														<input class="form-control form-control-lg form-control-solid" type="text" id="newpass">
													</div>
												</div>
												<!--end::Group-->
												<!--begin::Group-->
												<div class="form-group row">
													<label class="col-form-label col-4 text-lg-right text-left">Vérifier le mot de passe</label>
													<div class="col-8">
														<input class="form-control form-control-lg form-control-solid" type="text" id="confirmpass">
													</div>
												</div>
												<!--end::Group-->
											</div>
										</div>
										<!--end::Row-->
									</div>
									<!--end::Body-->
									<!--begin::Footer-->
									<div class="card-footer pb-0">
										<div class="row">
											<div class="col-xl-2"></div>
											<div class="col-xl-7">
												<div class="row">
													<div class="col-3"></div>
													<div class="col-9">
														<a class="btn btn-light-primary font-weight-bold" id="BTN_SAVE">Sauvegarder les modifications</a>
														<a href="#" class="btn btn-clean font-weight-bold" id="clear">Annuler</a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!--end::Footer-->
								</div>
								<!--end::Tab-->
							</div>
						</form>
					</div>
					<!--begin::Card body-->
				</div>
				<!--end::Card-->
			</div>
			<!--end::Container-->
		</div>
	</div>
</div>
		<!--end::Main-->
@endsection

{{-- Scripts Section --}}
@section('scripts')
	<script type="text/javascript">
		$("#popup").hide();

		$("#BTN_SAVE").click(function() {
			var email = document.querySelector('#email').value;
			var oldpass = document.querySelector('#oldpass').value;
			var newpass = document.querySelector('#newpass').value;
			var confirmpass = document.querySelector('#confirmpass').value;
			var formData=[];
			if (email && oldpass && newpass && confirmpass) {
				formData = formData.concat([{
					name: "email",
					value: email
				}, {
					name: "oldpass",
					value: oldpass
				},
				{
					name: "newpass",
					value: newpass
				},
				{
					name: "confirmpass",
					value: confirmpass
				}]);

			}
			
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				type: 'POST',
				url: '/updatepass',
				data: formData,
				dataType: 'JSON',
				success: function(result) {
					if(result.success){
						document.getElementById("popup1").style.display = 'block';
						document.getElementById("popup").style.display = 'none';
						$(".alert.alert-custom.alert-light-danger .alert-close i").css("color","#14603C");
					}else{
						document.getElementById("popup").style.display = 'block';
						document.getElementById("popup1").style.display = 'none';
					}
				}
			});
		});

		$("#clear").click(function() {
			$('#email').val("");
			document.getElementById("changepass").reset();
			window.location = "/";  
		});
		
            </script>
@endsection