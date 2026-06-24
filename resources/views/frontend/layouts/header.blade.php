<header class="navbar-main-header" style="background-image: url('{{ asset('assets/image/header-bk.webp') }}');">
    <div class="container navbar-container">
        <!-- Top Row -->
        <div class="navbar-top-row w-100">
            <div class="nav-icons ms-auto">
                @auth
                    @php
                        // ดึงจำนวนที่ยังไม่ได้อ่าน
                        $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')
                            ->where('user_id', Auth::id())
                            ->where('is_read', false)
                            ->count();

                        // ดึงรายการล่าสุด 5 รายการ
                        $recentNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                            ->where('user_id', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    <div class="dropdown notification-dropdown">
                        <button class="btn-dropdown dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: white; background: transparent; border: none; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-bell"></i>
                            <span>การแจ้งเตือน</span>

                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; margin-top: 5px; margin-left: -5px;">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 320px; max-height: 450px; overflow-y: auto; background-color: #ffffff; border-radius: 12px; padding: 0; border: 1px solid #eee; z-index: 9999 !important;">

                            <li style="background-color: var(--primary-navy, #1a2d5e); padding: 12px 15px; border-radius: 11px 11px 0 0; position: sticky; top: 0; z-index: 10;">
                                <h6 class="dropdown-header text-white fw-bold m-0 p-0" style="font-size: 0.95rem;"><i class="fas fa-bell me-2"></i>การแจ้งเตือนล่าสุด</h6>
                            </li>

                            @forelse($recentNotifications as $notify)
                                <li>
                                    <a class="dropdown-item py-3 border-bottom" href="javascript:void(0);" onclick="markNotifyAsRead({{ $notify->id }}, this)" style="white-space: normal; background-color: {{ $notify->is_read ? '#ffffff' : '#f8fafc' }}; color: #333; transition: background-color 0.3s;">

                                    <span id="notifyBadgeCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; margin-top: 5px; margin-left: -5px;" data-count="{{ $unreadCount }}">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>

                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold {{ $notify->type == 'promotion' ? 'text-danger' : ($notify->type == 'privilege' ? 'text-warning' : 'text-primary') }}">
                                                @if($notify->type == 'promotion') <i class="fas fa-bullhorn me-1"></i> โปรโมชัน
                                                @elseif($notify->type == 'privilege') <i class="fas fa-star me-1"></i> สิทธิพิเศษ
                                                @else <i class="fas fa-info-circle me-1"></i> ทั่วไป @endif
                                            </small>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                {{ \Carbon\Carbon::parse($notify->created_at)->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="fw-bold mt-1" style="font-size: 0.85rem; color: var(--primary-navy, #1a2d5e);">{{ $notify->title }}</div>
                                        <div class="text-muted mt-1" style="font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4;">
                                            {{ $notify->body }}
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item text-center text-muted py-4" style="background: white;">ไม่มีการแจ้งเตือนใหม่</span></li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-icon-item" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 5px; font-size: 0.85rem;"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                @endauth

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
            <div class="logo-container" style="position: relative; display: inline-block; max-width: 150px; width: 100%;">
                <img src="{{ asset('assets/image/logo.webp') }}" alt="AEG EASE CLUB" style="width: 100%; height: auto; display: block;">

                <a href="{{ route('home') }}"
                style="position: absolute; top: 0; left: 0; width: 40%; height: 100%; z-index: 10;"
                title="หน้าหลัก"></a>

                <a href="{{ route('rewards') }}"
                style="position: absolute; top: 0; right: 0; width: 60%; height: 100%; z-index: 10;"
                title="สิทธิพิเศษ EASE CLUB"></a>
            </div>

            <div class="search-container mx-lg-4 flex-grow-1 d-none d-md-block">
                <input type="text" class="search-input" placeholder="ค้นหาบริการหรือสินค้า...">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>

            <div class="cart-section">
                <a href="{{ route('cart') }}" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>

                <div class="points-badge shadow-sm">
                    <i class="fas fa-coins" style="color: #f1c40f;"></i>
                    {{ Auth::check() ? (\Illuminate\Support\Facades\DB::table('customer_wallets')->where('user_id', Auth::id())->value('current_points') ?? 0) : 0 }}
                </div>

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

<script>
function markNotifyAsRead(id, element) {
    // 1. ตรวจสอบว่าถ้ายังไม่ได้อ่าน (สีเทา) ถึงจะทำงาน
    if (element.style.backgroundColor !== 'rgb(255, 255, 255)' && element.style.backgroundColor !== '#ffffff') {

        // เปลี่ยนพื้นหลังเป็นสีขาวทันที (UX)
        element.style.backgroundColor = '#ffffff';

        // 2. ลดตัวเลขบนกระดิ่งแจ้งเตือน
        let badge = document.getElementById('notifyBadgeCount');
        if (badge) {
            let currentCount = parseInt(badge.getAttribute('data-count')) - 1;
            if (currentCount > 0) {
                badge.setAttribute('data-count', currentCount);
                badge.innerText = currentCount > 99 ? '99+' : currentCount;
            } else {
                badge.style.display = 'none'; // ซ่อนเลขถ้าเป็น 0
            }
        }

        // 3. ยิง AJAX ไปหา API เส้นที่พี่มีอยู่แล้ว
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).catch(err => console.error('Error marking notification as read:', err));
    }
}
</script>
