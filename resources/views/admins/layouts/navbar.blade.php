<!-- Main navbar -->
<div class="navbar navbar-expand-xl navbar-dark navbar-static px-0">
    <div class="d-flex flex-1 d-lg-none pl-3">
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-transmission"></i>
        </button>
    </div>
    <div class="d-lg-flex flex-1 d-none">
        <a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-lg-block">
            <i class="fa-solid fa-bars"></i>
        </a>
    </div>

    <div class="navbar-brand text-center text-lg-left d-lg-none">
        <a href="{{ route('home.index') }}" class="d-inline-block">
            <img src="{{ asset('global_assets/images/logo_light.png') }}" class="d-none d-sm-block" alt="">
            <img src="{{ asset('global_assets/images/logo_icon_light.png') }}" class="d-sm-none" alt="">
        </a>
    </div>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-nav-underline flex-row text-nowrap mx-auto">
            <li class="nav-item">
                <a href="{{ route('home.index') }}" class="navbar-nav-link">
                    <i class="fa-solid fa-qrcode mr-2"></i>
                    <span>Đổi sim - Lấy QR</span>
                </a>
            </li>
    
            <li class="nav-item">
                <a href="{{ route('swap-imei.index') }}" class="navbar-nav-link">
                    <i class="fa-solid fa-repeat mr-2"></i>
                    <span>Đảo SIM</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('toggle-gprs.index') }}" class="navbar-nav-link">
                    <i class="fa-solid fa-rss"></i>
                    <span>Đóng mở dịch vụ GPRS</span>
                </a>
            </li>

            <li class="nav-item dropdown nav-item-dropdown-xl">
                <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-chart-bar mr-2"></i>
                    Báo cáo đóng mở SIM
                </a>
            
                <div class="dropdown-menu dropdown-scrollable-xl">
                    <a href="{{ route('esim-report.import.index') }}" class="dropdown-item">
                        <span>Lấy báo cáo</span>
                    </a>
                    <a href="{{ route('esim-report.statistical.index') }}" class="dropdown-item">
                        <span>Xem thống kê</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>

    <div class="d-flex flex-1 justify-content-end pr-3">
        <ul class="navbar-nav flex-row order-1 order-lg-2 flex-1 flex-lg-0 justify-content-end align-items-center">
            <form id="logoutForm" action="{{ route('logout.post') }}" method="POST" class="d-none">
                @method('POST')
                @csrf
            </form>
            <a href="#!" class="navbar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                <i class="icon-switch2"></i>
            </a>
        </ul>
    </div>
</div>
<!-- /main navbar -->