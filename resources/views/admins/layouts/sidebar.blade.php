<!-- Main sidebar -->
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">

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
                        <button type="button" class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                            <i class="icon-transmission"></i>
                        </button>

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
                    <a href="{{ route('home.index') }}" class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-repeat"></i>
                        <span>Đổi sim - Lấy QR</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('msin-check.index') }}" class="nav-link {{ request()->routeIs('msin-check.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-sim-card"></i>
                        <span>Kiểm tra MSIN</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('mobile-check.index') }}" class="nav-link {{ request()->routeIs('mobile-check.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-phone"></i>
                        <span>Kiểm tra thuê bao</span>
                    </a>
                </li>

                </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->
    
</div>
<!-- /main sidebar -->