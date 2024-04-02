@php
	$direction = config('layout.extras.user.offcanvas.direction', 'right');
@endphp
 {{-- User Panel --}}
<div id="kt_quick_user" class="offcanvas offcanvas-{{ $direction }} p-10">
	{{-- Header --}}
	<div class="offcanvas-header d-flex align-items-center justify-content-between pb-5">
		<h3 class="font-weight-bold m-0">
			Mon Profil
		</h3>
		<a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
			<i class="ki ki-close icon-xs text-muted"></i>
		</a>
	</div>

	{{-- Content --}}
    <div class="offcanvas-content pr-5 mr-n5">
		{{-- Header --}}
        <div class="d-flex align-items-center mt-5">
            <div class="symbol symbol-100 mr-5">
                <div class="symbol-label" style="background-image:url('{{ asset('media/users/blank.png') }}')"></div>
				<i class="symbol-badge bg-success"></i>
            </div>
            <div class="d-flex flex-column">
                <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
					{{ Auth::user()->name }}
				</a>
                <div class="text-muted mt-1">
                    Application Developer
                </div>
                <div class="navi mt-2">
                    <a href="#" class="navi-item">
                        <span class="navi-link p-0 pb-2">
                            <span class="navi-icon mr-1">
								{{ Metronic::getSVG("media/svg/icons/Communication/Mail-notification.svg", "svg-icon-lg svg-icon-primary") }}
							</span>
                            <span class="navi-text text-muted text-hover-primary">{{ Auth::user()->email }}</span>
                        </span>
                    </a>

					<!-- Authentication -->
					<form method="POST" action="{{ route('logout') }}">
                        @csrf
						<a href="route('logout')" class="btn btn-sm btn-light-primary font-weight-bolder py-2 px-5" onclick="event.preventDefault();this.closest('form').submit();">Déconnexion</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Separator --}}
		<div class="separator separator-dashed mt-8 mb-5"></div>

		{{-- Nav --}}
        
		<div class="navi navi-spacer-x-0 p-0">
		    {{-- Item --}}
            <form  action="{{ url('/getnewpassword') }}">
                <!-- @csrf -->
                @method('put')
                @csrf
                <a href="{{ url('/getnewpassword') }}" class="navi-item">
                    <div class="navi-link">
                        <div class="symbol symbol-40 bg-light mr-3">
                            <div class="symbol-label">
                                {{ Metronic::getSVG("media/svg/icons/General/Notification2.svg", "svg-icon-md svg-icon-success") }}
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

