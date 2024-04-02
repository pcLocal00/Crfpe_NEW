{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="row">
    <div class="col-lg-2">
        <div class="card card-custom card-stretch">
            <div class="card-body">
                <div class="navi navi-bold navi-hover navi-active navi-link-rounded">
                    <div class="navi-item mb-2">
                        <a style="cursor:pointer;" id="NAV1" onclick="_loadContentImport(1)"
                           class="navi-link css-af py-4 active">
                        <span class="navi-icon mr-2">
                            <i class="flaticon2-accept"></i>
                        </span>
                            <span class="navi-text font-size-lg">Parcours Sup</span>
                        </a>
                    </div>
                    <div class="navi-item mb-2">
                        <a style="cursor:pointer;" id="NAV2" onclick="_loadContentImport(2)"
                           class="navi-link css-af py-4 active">
                        <span class="navi-icon mr-2">
                            <i class="flaticon2-accept"></i>
                        </span>
                            <span class="navi-text font-size-lg">Les prospects</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-10" id="BLOCK_CONTENT_NAVIGATION">

    </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.2.7/css/fileinput.min.css" integrity="sha512-qPjB0hQKYTx1Za9Xip5h0PXcxaR1cRbHuZHo9z+gb5IgM6ZOTtIH4QLITCxcCp/8RMXtw2Z85MIZLv6LfGTLiw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.2.7/js/fileinput.min.js" integrity="sha512-CCLv901EuJXf3k0OrE5qix8s2HaCDpjeBERR2wVHUwzEIc7jfiK9wqJFssyMOc1lJ/KvYKsDenzxbDTAQ4nh1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.2.7/js/locales/fr.min.js" integrity="sha512-j2qSQy+GP+xUW3zcxwZakZltSjfXlALQXhObpRc8Xc+uhgOJu0o5FQJDphQRRm9yM0LDdBMoNEW/jnsX2kXkYA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=0') }}"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    function _loadContentImport(viewtype) {
                var block_id = 'BLOCK_CONTENT_NAVIGATION';
                KTApp.block('#' + block_id, {
                    overlayColor: '#000000',
                    state: 'danger',
                    message: 'S\'il vous plaît, attendez...'
                });
                $.ajax({
                    url: '/view/import/' + viewtype,
                    type: 'GET',
                    dataType: 'html',
                    success: function (html, status) {
                        $('#' + block_id).html(html);
                    },
                    error: function (result, status, error) {},
                    complete: function (result, status) {
                        KTApp.unblock('#' + block_id);
                    }
                });
                $(".css-af").each(function () {
                    $(this).removeClass("active");
                });
                btn_id = 'NAV'+viewtype; 
                $('#' + btn_id).addClass("active");
    }
    _loadContentImport(1);
</script>
@endsection

