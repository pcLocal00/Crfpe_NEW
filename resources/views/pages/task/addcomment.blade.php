
{{-- Extends layout --}}
@extends('layout.default')


{{-- Styles Section --}}
@section('styles')
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">

<style>
	.CodeMirror-scroll{
		height: 100px;
	}
	.select2-container{
		width: 100% !important;
	}
	.rappelmode{
		width: 22px;
		height: 15px;
	}
</style>
@endsection

{{-- Content --}}
@section('content')
<!--begin::Card-->
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
					<span class="nav-text font-size-lg" style="margin-left: 10px;">Créer un ou plusieurs commentaires</span>
				</a>
			</li>
			<!--end::Item-->
		</ul>
	</div>
						
	<form class="form" id="addtask">
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
										<div class="alert-text font-weight-bold">Merci de bien vouloir vérifier vos données.</div>
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
										<div class="alert-text font-weight-bold" style="color: #14603C;">Votre commentaire a été créée avec avec succès!</div>
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
							<div id="room_fileds">
								<div class="form-group row">
									<label class="col-form-label col-4 text-lg-right text-left">Commentaire</label>
									<div class="col-8">
										<textarea class="form-control form-control-solid comment" name="notes[]" id="notes"></textarea>
									</div>
								</div>
							</div>
							<div class="form-group row" style="float: right;">
								<a class="btn btn-clean font-weight-bold btn-sm" onclick="other()"><i class="ki ki-plus icon-sm"></i>Ajouter un autre</a>								
							</div>
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
									<a class="btn btn-light-primary font-weight-bold" id="BTN_SAVE">Créer</a>
									<a href="#" class="btn btn-clean font-weight-bold" id="clear" onclick="reset()">Réinitialiser</a>
									<a href="#" class="btn btn-clean font-weight-bold" id="clear" onclick="refresh()">Annuler</a>
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
	
	//textarea
	const easyMDE = new EasyMDE({element: document.getElementById('myTextarea')});

	function reset() {
        $(':input').val('');
	}

    function refresh() {
        window.location='/';
    }
	
	var room = 1;
	function other() {
		room++;
		var objTo = document.getElementById('room_fileds')
		var divtest = document.createElement("div");
		divtest.innerHTML = '<div class="form-group row" id="room_fileds"><label class="col-form-label col-4 text-lg-right text-left"></label><div class="col-8"><textarea class="form-control form-control-solid comment" name="notes[]" id="notes"></textarea></div></div>';
		
		objTo.appendChild(divtest)
    }

	$("#BTN_SAVE").click(function() {
			var comment=[];
			const form = new FormData();

			var id=<?php echo $row->id; ?>;

			jQuery("textarea.comment").each(function(){
				form.append('a', jQuery(this).val())
			})

			comment=form.getAll('a');
	
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				type: 'POST',
				url: '/api/sdt/addcomments',
				data: {"taskid": id,"comments": comment},
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
</script>
@endsection