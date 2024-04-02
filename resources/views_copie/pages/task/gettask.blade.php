
{{-- Extends layout --}}
@extends('layout.default')


{{-- Styles Section --}}
@section('styles')
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" /> -->
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">

<style>
	.CodeMirror-scroll{
		height: 100px;
	}
	.select2-container{
		width: 100% !important;
	}
	.comment-content{
		overflow: hidden;
		background: #F3F6F9;
		color: #3F4254;
		padding: 1rem;
		border-radius: 8px;
		z-index: 1;
		position: relative;
	}

	/*previous button*/

	#back{
		text-decoration: none;
		color: #3F4254;
		background: white;
		padding: 5px 46px;
		border-radius: 4px;  
		font-size: 23px;
	}
	#back:hover{
		background: #3F425478;
	}
	#back:focus{
		outline: 3px solid rgb(220 53 69 / 50%);
	
	}
	#backdiv{
		justify-content: space-between;
	}
</style>
@endsection


{{-- Content --}}
@section('content')
<!--begin::Card-->	
<div id="backdiv" class="mb-3">
    <a id="back" onclick="getreturn()" title="Ramener l'écran précédant">&#8249;&#8249;</a>
</div>		
<div class="card card-custom card-body">
	<div class="card-toolbar">
		<ul class="nav nav-tabs nav-bold nav-tabs-line nav-tabs-line-3x">
			<!--end::Item-->
			<!--begin::Item-->
			<li class="nav-item mr-3">
				<a class="nav-link active" data-toggle="tab" href="#kt_user_edit_tab_3">
					<span class="nav-icon">
						<span class="svg-icon">
							<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Communication/Shield-user.svg-->
							<img style="width: 27px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAADB0lEQVRoge2azUtUURTAf2eUGZpxkYZQQ8s2IS6bNCv6cBcIhbVTCNIxg2hVGpRQ/0GLYhKhsk1GC0Mwwj7AyDTaJO2Gdir04bTQ8CPfaTEOMwzzdN7c98ZB57canvede37v3PPm+t7ANkHcCNLwUFvWlAcCYSfnKcxWCJc/dcpL0xx8pgEACpEAEAhbyn03crCtSN2Q+kMJ7gJtwL58gk1FxVGFIzHVfMYpzIowuLib298uyEquMZV2J4f+cAe47iQxrxAIo9yoSqBAb64xtiKqtAmgQtPnTvm40UT5Xlk7Nqvk4ZgeVRi3oB0bEdseSa35zSSKwWRUPkA6p1zYViQb06teaOx8+86Vu1YpkHdFUuS6QodiOiMQLrBqM1NR2Z990GksVyriU7qBmQJOnfEJ3W7k4LgiuZjskmFg2I1YhbJteqQsUmqURUoNV+5aTjhwTwM1AXqBK4AoDCaW6YlflWWTuEUVWZd4AZxJHRO4tifAUtxmM5gvRVtaWRKLmX9TuGQavygiWRK/xMcRVW5lDLFM5/BcJIfE6ckO+VopDJOuzCPTeTwVqRtSf3WAIZISP1MSDf16cA1eAyHgVdBPn+lcnonUDak/mOC5QAtJieaUhGXxFthLUuLs+4uyZDqfJyLFlgAPRLZCAlwWyZaoIN0TXkqAiyK5JCaiMp0pITDqhQS4JJKvxC4/57yQAJe2KKEET8noiYkOmW6Maf2axRugFmXk9wqt8ajZfmojjCvSNKBhoBX4m+qJxpjWr5GWmF+h1XRTuBnGIqurNJN8hvwutZzWv+xqBUaDAc57LQFuLC2hGUCFUCSmI5bFSSBYjOWUiRs9cgpAlBOpAwLPFmpoj9s8OfcCcxHluwoiwpjA2D9l7EtU5lzIzRHGIlNdctyNREzZNv+zl0VKjbJIqeH4ruXlmysTdl5FnL5DLza2FVGYheSr4eKlk5tIvx5b/zhvN8a2Ij54otCjMB6JbXFbpB/fDdgNsRVZqKavKgEWtBfyOxOX+QE8Ds5xc4vzKLPz+A8LnzkXUbeiTgAAAABJRU5ErkJggg==">
							<!--end::Svg Icon-->
						</span>
					</span>
					<span class="nav-text font-size-lg" style="margin-left: 10px;">Détails de cette tâche</span>
				</a>
			</li>
			<!--end::Item-->
		</ul>
	</div>
	<div class="tab-content">
		<div class="tab-pane px-7 active" id="kt_user_edit_tab_3" role="tabpanel">
			<!--begin::Body-->
			<div class="card-body">
				<!--begin::Row-->
				<div class="row">
					<div class="col-xl-2"></div>
					<div class="col-xl-7">
						<!--end::Row-->
						<!--begin::Row-->
						<div class="row">
							<label class="col-4"></label>
							<div class="col-8">
								<!-- <h6 class="text-dark font-weight-bold mb-10">Modifier ou récupérer votre mot de passe :</h6> -->
							</div>
						</div>
						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Résumé</label>
							<div class="col-8">
								<input class="form-control form-control-lg form-control-solid" type="text" id="resume" value="{{ ($row)?$row->title:'' }}" readonly>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Type</label>
							<div class="col-8">
								<select id="type" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->type)) { echo ($row->type);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>


						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Source</label>
							<div class="col-8">
								<select id="source" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->source)) { echo ($row->source);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">État</label>
							<div class="col-8">
								<select id="etat" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->etat)) { echo ($row->etat);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>

						<!--end::Row-->
						<!--begin::Group-->
						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Description</label>
							<div class="col-8">
								<textarea id="myTextarea" readonly><?php if (isset($row->description)) { echo htmlspecialchars($row->description);} ?></textarea>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Superviseur</label>
							<div class="col-8">
								<select id="rapporteur" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->apporteur)) { echo ($row->apporteur);} else { echo '--------------';} ?> </option>                                                      
								</select>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Responsable</label>
							<div class="col-8">
								<select id="responsable" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->responsable)) { echo ($row->responsable);} else { echo '--------------';} ?> </option>                                                      
								</select>
							</div>
						</div>

						<!--end::Group-->
						<!--begin::Group-->

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Date de début</label>
							<div class="col-8">
								<div class="position-relative d-flex align-items-center" style="align-items: center !important;">
									<input class="form-control form-control-solid ps-12 flatpickr-input" id="datedebut" value="{{ ($row)?$row->start_date:'' }}" placeholder="         --- Sélectionner une date de début ---" name="due_date" type="text" readonly="readonly" style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Date d'échéance</label>
							<div class="col-8">
								<div class="position-relative d-flex align-items-center" style="align-items: center !important;">
									<input class="form-control form-control-solid ps-12 flatpickr-input" id="dateecheance" value="{{ ($row)?$row->ended_date:'' }}" placeholder="         --- Sélectionner une date d'échéance ---" name="due_date" type="text" readonly="readonly" style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Date de rappel</label>
							<div class="col-8">
								<div class="position-relative d-flex align-items-center" style="align-items: center !important;">
									<input class="form-control form-control-solid ps-12 flatpickr-input" id="dateecheance" value="{{ ($row)?$row->callback_date:'' }}" placeholder="         --- Sélectionner une date d'échéance ---" name="due_date" type="text" readonly="readonly" style="background-color: #F5F8FA; border-color: #F5F8FA; color: #5E6278;">
								</div>
							</div>
						</div>

						<!-- <div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Pièce jointe</label>
							<div class="col-8">
								<div class="position-relative d-flex align-items-center" style="align-items: center !important;-webkit-box-pack: center;justify-content: center;border: 1px dashed rgb(193, 199, 208);">
									<div class="sc-1apavjx-0" id="attachment-dropzone-container" style="display: flex;">
										<span class="sc-1apavjx-2" style="display: flex;-webkit-box-align: center;align-items: center;overflow: hidden;">
											<span role="img" aria-label="Upload" class="css-pxzk9z" style="--icon-primary-color:#7A869A; --icon-secondary-color:var(--ds-surface, #FFFFFF);padding: 12px;color: rgb(122, 134, 154);">
											<svg width="24" height="24" viewBox="0 0 24 24" role="presentation"><g fill="currentColor" fill-rule="evenodd"><path d="M11.208 9.32L9.29 11.253a1 1 0 000 1.409.982.982 0 001.397 0l1.29-1.301 1.336 1.347a.982.982 0 001.397.001 1.002 1.002 0 00.001-1.408l-1.965-1.98a1.08 1.08 0 00-1.538-.001z"></path><path d="M11 10.007l.001 9.986c0 .557.448 1.008 1 1.007.553 0 1-.45 1-1.007L13 10.006C13 9.451 12.552 9 12 9s-1.001.451-1 1.007z"></path><path d="M7.938 5.481a4.8 4.8 0 00-.777-.063C4.356 5.419 2 7.62 2 10.499 2 13.408 4.385 16 7.1 16h2.881v-1.993H7.1c-1.657 0-3.115-1.663-3.115-3.508 0-1.778 1.469-3.087 3.104-3.087h.012c.389 0 .686.051.97.15l.17.063c.605.248.875-.246.875-.246l.15-.267c.73-1.347 2.201-2.096 3.716-2.119a4.14 4.14 0 014.069 3.644l.046.34s.071.525.665.525c.013 0 .012.005.023.005h.254c1.136 0 1.976.959 1.976 2.158 0 1.207-.987 2.342-2.07 2.342h-3.964V16h3.964C20.105 16 22 13.955 22 11.665c0-1.999-1.312-3.663-3.138-4.074-.707-2.707-3.053-4.552-5.886-4.591-1.975.021-3.901.901-5.038 2.481z"></path></g>
											</svg>
										</span>
										<div class="parent-div" sstyle="display: inline-block;position: relative;overflow: hidden;">
											<button tabindex="0" style="-webkit-box-align: baseline;align-items: baseline;border-width: 0px;border-radius: 3px;box-sizing: border-box;display: inline-flex;font-size: inherit;font-style: normal;font-family: inherit;font-weight: 500;max-width: 100%;position: relative;text-align: center;text-decoration: none;transition: background 0.1s ease-out 0s, box-shadow 0.15s cubic-bezier(0.47, 0.03, 0.49, 1.38) 0s;white-space: nowrap;background: none;cursor: pointer;height: auto;line-height: inherit;padding: 0px;vertical-align: baseline;width: auto;color: var(--ds-link,#0052CC) !important;"><span >parcourir</span></button>
											<input type="file" id="fileupload" name='fileupload' multiple="" data-preview-file-type="any" style="left: 0;top: 0; opacity: 0;position: absolute;font-size: 90px;">
										</div>
									</div>
								</div>
							</div>
						</div> -->


						
						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Type</label>
							<div class="col-8">
								<select id="type" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->type)) { echo ($row->type);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>
												
						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Mode de rappel</label>
							<div class="col-8 checkbox-inline">				
								<label class="checkbox">
								<?php if (isset($row->callback_mode) && ($row->callback_mode=="email")) { echo '<input type="checkbox" name="rappelmode" value="email" checked>';} else { echo '<input type="checkbox" name="rappelmode" value="email">';} ?>
								<span></span>Email</label>
								<label class="checkbox">
								<?php if (isset($row->callback_mode) && ($row->callback_mode=="solaris")) { echo '<input type="checkbox" name="rappelmode" value="solaris" checked>';} else { echo '<input type="checkbox" name="rappelmode" value="solaris">';} ?>
								<span></span>Solaris</label>
								<label class="checkbox">
								<?php if (isset($row->callback_mode) && ($row->callback_mode=="phone")) { echo '<input type="checkbox" name="rappelmode" value="phone" checked>';} else { echo '<input type="checkbox" name="rappelmode" value="phone">';} ?>
								<span></span>Phone</label>
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Mode de réponse</label>
							<div class="col-8">
								<select id="mode" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->reponse)) { echo ($row->reponse);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>



						<div class="form-group row glblaf">
							<label class="col-form-label col-4 text-lg-right text-left">Action de formation</label>
							<div class="col-8">
								<select id="aflist">
									<option value=""><?php if (isset($row->af)) { echo ($row->af);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>

						<div class="form-group row glblpf">
							<label class="col-form-label col-4 text-lg-right text-left">Produit de formation</label>
							<div class="col-8">
								<select id="pflist">
									<option value=""><?php if (isset($row->pf)) { echo ($row->pf);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>	
						<div class="form-group row glblentite">
							<label class="col-form-label col-4 text-lg-right text-left">Entité</label>
							<div class="col-8">
								<select id="entitelist">
									<option value=""><?php if (isset($row->entitie)) { echo ($row->entitie);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>	

						<div class="form-group row glblcontact">
							<label class="col-form-label col-4 text-lg-right text-left">Contact</label>
							<div class="col-8">
								<select id="contactlist">
									<option value=""><?php if (isset($row->contact)) { echo ($row->contact->firstname.' '.$row->contact->lastname);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>	

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Commentaire</label>
							<div class="col-8">
								@if($row->comment)
								<div class="comment-content">
									@foreach($row->comment as $key=>$sp)
									> {{ $sp }} <br/>
									@endforeach
								</div>
								@endif
							</div>
						</div>

						<div class="form-group row">
							<label class="col-form-label col-4 text-lg-right text-left">Priorité</label>
							<div class="col-8">
								<select id="priorite" class="form-control form-control-lg form-control-solid">
									<option value=""><?php if (isset($row->priority)) { echo ($row->priority);} else { echo '--------------';} ?> </option>
								</select>
							</div>
						</div>
						
						<!--end::Group-->
					</div>
				</div>
				<!--end::Row-->
			</div>
			<!--end::Body-->
			<!--begin::Footer-->
			<!--end::Footer-->
		</div>
		<!--end::Tab-->
	</div>
</div>
<!--end::Card-->

@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js" crossorigin="anonymous"></script>

{{-- page scripts --}}
<script>

	$(document).ready(function () {
		$("select").select2();
	});

	$(function() {
		$('.flatpickr-input').datepicker({
			language: 'fr',
			format: 'dd/mm/yyyy',
			todayHighlight: true,
			orientation: "bottom left",
			templates: {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			},
			lang: 'fr'
		})
	});

    //textarea
    const easyMDE = new EasyMDE({element: document.getElementById('myTextarea')});

	function getreturn(){
		history.back();
		window.location='/';
	}
</script>
@endsection