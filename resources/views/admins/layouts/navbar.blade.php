<!-- Main navbar -->
<div class="navbar navbar-expand-xl navbar-dark navbar-static px-0">
    <div class="d-flex flex-1 d-lg-none pl-3">
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-transmission"></i>
        </button>
    </div>

    <div class="d-flex flex-1 pl-3">
        <div class="navbar-brand wmin-0 mr-1">
            <a href="{{ route('home.index') }}" class="d-inline-block">
                <img src="{{ asset('global_assets/images/logo_light.png') }}" class="d-none d-sm-block" alt="">
                <img src="{{ asset('global_assets/images/logo_icon_light.png') }}" class="d-sm-none" alt="">
            </a>
        </div>
    </div>

    <div class="d-none d-lg-flex w-100 w-xl-auto overflow-auto overflow-xl-visible scrollbar-hidden border-top border-top-xl-0 order-1 order-xl-0">
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

            <li class="nav-item dropdown nav-item-dropdown-xl">
                <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i>
                    Tra cứu
                </a>
            
                <div class="dropdown-menu dropdown-scrollable-xl">
                    <a href="{{ route('msin-check.index') }}" class="dropdown-item rounded">
                        <i class="fa-solid fa-sim-card"></i>
                        <span>Kiểm tra MSIN</span>
                    </a>
                    <a href="{{ route('mobile-check.index') }}" class="dropdown-item rounded">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Kiểm tra thuê bao</span>
                    </a>
                    <a href="{{ route('mi-check.index') }}" class="dropdown-item rounded">
                        <i class="fa-solid fa-signal"></i>
                        <span>Tra cứu dung lượng</span>
                    </a>
                </div>
            </li>

            <li class="nav-item dropdown nav-item-dropdown-xl">
                <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-toggle-on mr-2"></i>
                    Đóng mở dịch vụ
                </a>
            
                <div class="dropdown-menu dropdown-scrollable-xl">
                    <a href="{{ route('toggle-serivce.index') }}" class="dropdown-item rounded">
                        <i class="fa-solid fa-rss"></i>
                        <span>Đóng mở dịch vụ GPRS</span>
                    </a>
                    <a href="{{ route('toggle-ioc.index') }}" class="dropdown-item rounded">
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>Cắt mở IOC</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>

    <div class="d-flex flex-xl-1 justify-content-xl-end order-0 order-xl-1 pr-3">
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

@push('scripts')
<script>
    let current_url = "{{ url()->current() }}";
    let nav_link = $(`a[href="${current_url}"]`);

    if (nav_link.hasClass('dropdown-item')) {
        nav_link.parent().prev('a.navbar-nav-link.dropdown-toggle').addClass('active');
    }

    nav_link.addClass('active');
</script>
@endpush