<header class="navbar-main-header" style="background-image: url('{{ asset('assets/image/header-bk.webp') }}');">
    <div class="container navbar-container">
        <!-- Top Row -->
        <div class="navbar-top-row w-100">
            <div class="nav-icons ms-auto">
                <a href="{{ route('repair-status') }}" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                
                <!-- เช็คสถานะล็อกอิน -->
                <div class="dropdown header-dropdown">
                    <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                        <span>
                            @auth
                                {{ Auth::user()->name }}
                            @else
                                ข้อมูลของฉัน
                            @endauth
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @auth
                            <li><a class="dropdown-item" href="{{ route('my-account') }}"><i class="fas fa-id-card-alt me-2"></i>ข้อมูลของฉัน</a></li>
                            <li><a class="dropdown-item" href="{{ route('repair-history') }}"><i class="fas fa-tools me-2"></i>ประวัติการแจ้งซ่อม</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li>
                                <a class="dropdown-item text-warning" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                                </a>
                                <form id="logout-form" action="{{ route('logout.post') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</a></li>
                            <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i>ลงทะเบียน</a></li>
                        @endauth
                    </ul>
                </div>

                <span style="color: rgba(255,255,255,0.5);">|</span>

                <div class="dropdown header-dropdown">
                    <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://flagcdn.com/w20/th.png" alt="TH" width="20">
                        <span>TH</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><img src="https://flagcdn.com/w20/th.png" width="18"> Thai (TH)</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><img src="https://flagcdn.com/w20/gb.png" width="18"> English (EN)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="navbar-bottom-row w-100 mt-2">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('assets/image/logo.webp') }}" alt="AEG Logo" onerror="this.src='https://via.placeholder.com/150x50?text=AEG+LOGO'">
            </a>

            <div class="search-container mx-lg-4 flex-grow-1 d-none d-md-block">
                <input type="text" class="search-input" placeholder="ค้นหาบริการหรือสินค้า...">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>

            <div class="cart-section">
                <a href="{{ route('cart') }}" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
                <div class="points-badge shadow-sm"><i class="fas fa-coins" style="color: #f1c40f;"></i> {{ Auth::check() ? (Auth::user()->points ?? 0) : 0 }}</div>
                <button class="navbar-toggler d-lg-none ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenuCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </div>
</header>

<nav class="main-navigation-bar sticky-top">
    <div class="container">
        <div class="collapse navbar-collapse d-lg-block" id="mainMenuCollapse">
            <ul class="navbar-nav d-flex flex-column flex-lg-row justify-content-center text-center">
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">หน้าหลัก</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->is('products/equipment') ? 'active' : '' }}" href="{{ route('products', 'equipment') }}">สินค้าพร้อมติดตั้ง</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->is('products/package') ? 'active' : '' }}" href="{{ route('products', 'package') }}">แพ็กเกจบริการ</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">บริการแนะนำ</a></li>
            </ul>
        </div>
    </div>
</nav>