<!-- Main sidebar -->
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg sidebar-collapsed">

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-section sidebar-user my-1">
            <div class="sidebar-section-body">
                <div class="media">
                    <span class="mr-3">
                        <img src="{{ asset('global_assets/images/placeholders/placeholder.jpg') }}" class="rounded-circle" alt="">
                    </span>

                    <div class="media-body">
                        <div class="font-weight-semibold">CNPT</div>
                    </div>

                    <div class="ml-3 align-self-center">
                        <button type="button" class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-mobile-main-toggle d-lg-none">
                            <i class="icon-cross2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <!-- Main -->
                <li class="nav-item-header">
                    <div class="text-uppercase font-size-xs line-height-xs">Menu</div> 
                    <i class="icon-menu" title="Main"></i>
                </li>

                <li class="nav-item">
                    <a href="{{ route('home.index') }}" class="nav-link">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Đổi sim - Lấy QR</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('swap-imei.index') }}" class="nav-link">
                        <i class="fa-solid fa-repeat"></i>
                        <span>Đảo SIM</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('msin-check.index') }}" class="nav-link">
                        <i class="fa-solid fa-sim-card"></i>
                        <span>Kiểm tra MSIN</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('mobile-check.index') }}" class="nav-link">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Kiểm tra thuê bao</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('mi-check.index') }}" class="nav-link">
                        <i class="fa-solid fa-signal"></i>
                        <span>Tra cứu dung lượng</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('toggle-gprs.index') }}" class="nav-link">
                        <i class="fa-solid fa-rss"></i>
                        <span>Đóng mở dịch vụ GPRS</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('toggle-smt-smo.index') }}" class="nav-link">
                        <i class="fa-solid fa-comment-sms"></i>
                        <span>Đóng mở SMT/SMO</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('toggle-services.index') }}" class="nav-link">
                        <i class="fa-solid fa-headset"></i>
                        <span>Đóng mở các dịch vụ khác</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('toggle-ioc.index') }}" class="nav-link">
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>Cắt mở IOC</span>
                    </a>
                </li>

                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link">
                        <i class="fa-solid fa-chart-bar"></i>
                        <span>Báo cáo cắt mở SIM</span>
                    </a>

                    <ul class="nav nav-group-sub" data-submenu-title="Layouts" style="display: none;">
                        <li class="nav-item">
                            <a href="{{ route('esim-report.import.index') }}" class="nav-link">Lấy báo cáo</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('esim-report.statistical.index') }}" class="nav-link">Xem thống kê</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('subscriber-check.index') }}" class="nav-link">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Tra cứu thông tin thuê bao</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('subscriber-check-ccos.index') }}" class="nav-link">
                        <i class="fa-solid fa-eye"></i>
                        <span>Tra cứu thuê bao CCOS</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->
    
</div>
<!-- /main sidebar -->

@push('scripts')
<script>
    let current_url = "{{ url()->current() }}";
    let nav_link = $('.nav-sidebar, .navbar-nav').find(`a[href="${current_url}"]`);

    if (nav_link.hasClass('dropdown-item')) {
        nav_link.parent().prev('a.navbar-nav-link.dropdown-toggle').addClass('active');
    }
    
    nav_link.closest('.nav-group-sub').css('display', 'block');
    nav_link.closest('.nav-item-submenu').addClass('nav-item-open');

    nav_link.addClass('active');
</script>
@endpush