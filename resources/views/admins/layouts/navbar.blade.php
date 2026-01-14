<!-- Main navbar -->
<div class="navbar navbar-expand-lg navbar-dark navbar-static">
    <div class="d-flex flex-1 d-lg-none">
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-transmission"></i>
        </button>
    </div>

    <div class="navbar-brand text-center text-lg-left">
        <a href="{{ route('home.index') }}" class="d-inline-block">
            <img src="{{ asset('global_assets/images/logo_light.png') }}" class="d-none d-sm-block" alt="">
            <img src="{{ asset('global_assets/images/logo_icon_light.png') }}" class="d-sm-none" alt="">
        </a>
    </div>

    <div class="collapse navbar-collapse order-2 order-lg-1" id="navbar-mobile">

    </div>

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
<!-- /main navbar -->