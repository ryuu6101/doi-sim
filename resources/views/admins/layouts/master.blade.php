<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Đổi SIM - QRCode SESIM</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
	<link href="{{ asset('global_assets/css/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('assets/css/all.min.css') }}" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
	<script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script src="{{ asset('global_assets/js/plugins/visualization/d3/d3.min.js') }}"></script>
	<script src="{{ asset('global_assets/js/plugins/visualization/d3/d3_tooltip.js') }}"></script>
	<script src="{{ asset('global_assets/js/plugins/ui/moment/moment.min.js') }}"></script>
	<script src="{{ asset('global_assets/js/plugins/pickers/daterangepicker.js') }}"></script>
	<script src="{{ asset('global_assets/js/plugins/notifications/noty.min.js') }}"></script>

	<script src="{{ asset('assets/js/app.js') }}"></script>
	<!-- /theme JS files -->

    @stack('styles')
</head>

<body>

    @include('admins.layouts.navbar')

	<!-- Page content -->
	<div class="page-content">

		@include('admins.layouts.sidebar')

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Inner content -->
			<div class="content-inner">

				@if (request()->is('ccos/*'))
				@include('admins.layouts.header-cookie')
				@else
				@include('admins.layouts.header')
				@endif

				<!-- Content area -->
				<div class="content">

					@yield('content')

				</div>

				@include('admins.layouts.footer')

			</div>

		</div>

	</div>

	@stack('modals')

	<script>
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$(document).ajaxError(function(event, xhr, settings, thrownError) {
			if (xhr.status === 419) {
				// Redirect to the current page to refresh the token
				alert('Vui lòng tải lại trang!');
				window.location.reload();
			}
		});
	</script>

	<script>
		// Override Noty defaults
        Noty.overrideDefaults({
            theme: 'limitless',
            layout: 'topRight',
            type: 'alert',
            timeout: 2500
        });

		function noty(message, type) {
			new Noty({
				text: message,
				type: type
			}).show();
		}
	</script>

	@if (session('success'))
	<script>noty("{{ session('success') }}", 'success');</script>
	@endif

	@if (session('error'))
	<script>noty("{{ session('error') }}", 'error');</script>
	@endif

    @stack('scripts')

</body>
</html>