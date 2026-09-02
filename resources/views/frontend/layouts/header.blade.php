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
                            <span>{{ __('การแจ้งเตือน') }}</span>

                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; margin-top: 5px; margin-left: -5px;">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 320px; max-height: 450px; overflow-y: auto; background-color: #ffffff; border-radius: 12px; padding: 0; border: 1px solid #eee; z-index: 9999 !important;">

                            <li style="background-color: var(--primary-navy, #1a2d5e); padding: 12px 15px; border-radius: 11px 11px 0 0; position: sticky; top: 0; z-index: 10;">
                                <h6 class="dropdown-header text-white fw-bold m-0 p-0" style="font-size: 0.95rem;"><i class="fas fa-bell me-2"></i>{{ __('การแจ้งเตือนล่าสุด') }}</h6>
                            </li>

                            @forelse($recentNotifications as $notify)
                                <li>
                                    <a class="dropdown-item py-3 border-bottom" href="javascript:void(0);" onclick="markNotifyAsRead({{ $notify->id }}, this)" style="white-space: normal; background-color: {{ $notify->is_read ? '#ffffff' : '#f8fafc' }}; color: #333; transition: background-color 0.3s;">

                                    <span id="notifyBadgeCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; margin-top: 5px; margin-left: -5px;" data-count="{{ $unreadCount }}">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>

                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold {{ $notify->type == 'promotion' ? 'text-danger' : ($notify->type == 'privilege' ? 'text-warning' : 'text-primary') }}">
                                                @if($notify->type == 'promotion') <i class="fas fa-bullhorn me-1"></i> {{ __('โปรโมชัน') }}
                                                @elseif($notify->type == 'privilege') <i class="fas fa-star me-1"></i> {{ __('สิทธิพิเศษ') }}
                                                @else <i class="fas fa-info-circle me-1"></i> {{ __('ทั่วไป') }} @endif
                                            </small>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                {{ \Carbon\Carbon::parse($notify->created_at)->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="fw-bold mt-1" style="font-size: 0.85rem; color: var(--primary-navy, #1a2d5e);">{{ $notify->title }}</div>
                                        <div class="text-muted mt-1" style="font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4;">
                                            {!! \App\Support\TextUtils::linkify($notify->body) !!}
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item text-center text-muted py-4" style="background: white;">{{ __('ไม่มีการแจ้งเตือนใหม่') }}</span></li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-icon-item" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 5px; font-size: 0.85rem;"><i class="fas fa-bell"></i><span>{{ __('การแจ้งเตือน') }}</span></a>
                @endauth

                <!-- เช็คสถานะล็อกอิน -->
                <div class="dropdown header-dropdown">
                    <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                        <span>
                            @auth
                                {{ Auth::user()->name }}
                            @else
                                {{ __('ข้อมูลของฉัน') }}
                            @endauth
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @auth
                            <li><a class="dropdown-item" href="{{ route('my-account') }}"><i class="fas fa-id-card-alt me-2"></i>{{ __('ข้อมูลของฉัน') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('repair-history') }}"><i class="fas fa-tools me-2"></i>{{ __('ประวัติการแจ้งซ่อม') }}</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li>
                                <a class="dropdown-item text-warning" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('ออกจากระบบ') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout.post') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-2"></i>{{ __('เข้าสู่ระบบ') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i>{{ __('ลงทะเบียน') }}</a></li>
                        @endauth
                    </ul>
                </div>

                <span style="color: rgba(255,255,255,0.5);">|</span>

                <div class="dropdown header-dropdown">
                    <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://flagcdn.com/w20/{{ app()->getLocale() === 'en' ? 'gb' : 'th' }}.png" alt="{{ strtoupper(app()->getLocale()) }}" width="20">
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('language.switch', 'th') }}"><img src="https://flagcdn.com/w20/th.png" width="18"> {{ __('Thai (TH)') }}</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('language.switch', 'en') }}"><img src="https://flagcdn.com/w20/gb.png" width="18"> {{ __('English (EN)') }}</a></li>
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
                title="{{ __('หน้าหลัก') }}"></a>

                <a href="{{ route('rewards') }}"
                style="position: absolute; top: 0; right: 0; width: 60%; height: 100%; z-index: 10;"
                title="{{ __('สิทธิพิเศษ EASE CLUB') }}"></a>
            </div>

            <div class="search-container mx-lg-4 flex-grow-1 d-none d-md-block" style="position: relative;">
                <input type="text" id="headerSearchInput" class="search-input" placeholder="{{ __('ค้นหาบริการหรือสินค้า...') }}">
                <button type="button" id="headerSearchBtn" class="search-btn"><i class="fas fa-search"></i></button>
                <div id="headerSearchResults" class="header-search-results"></div>
            </div>

            <div class="cart-section">
                <a href="{{ route('cart') }}" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>

                <div class="dropdown header-dropdown">
                    <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @auth
                            <li><a class="dropdown-item" href="{{ route('my-account') }}"><i class="fas fa-id-card-alt me-2"></i>{{ __('ข้อมูลของฉัน') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('repair-history') }}"><i class="fas fa-tools me-2"></i>{{ __('ประวัติการแจ้งซ่อม') }}</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li>
                                <a class="dropdown-item text-warning" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('ออกจากระบบ') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout.post') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-2"></i>{{ __('เข้าสู่ระบบ') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fas fa-user-plus me-2"></i>{{ __('ลงทะเบียน') }}</a></li>
                        @endauth
                    </ul>
                </div>

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
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('หน้าหลัก') }}</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->is('products/equipment') ? 'active' : '' }}" href="{{ route('products', 'equipment') }}">{{ __('สินค้าพร้อมติดตั้ง') }}</a></li>
                <!-- <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->is('products/package') ? 'active' : '' }}" href="{{ route('products', 'package') }}">แพ็กเกจบริการ</a></li> -->
                <li class="nav-item"><a class="nav-link nav-link-custom {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">{{ __('แพ็กเกจบริการ') }}</a></li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .header-search-results {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        max-height: 420px;
        overflow-y: auto;
        z-index: 2000;
        text-align: left;
    }
    .header-search-results .hs-group-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #999;
        padding: 10px 15px 4px;
    }
    .header-search-results .hs-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        text-decoration: none;
        color: #333;
    }
    .header-search-results .hs-item:hover { background: #f5f6f8; }
    .header-search-results .hs-item img {
        width: 40px; height: 40px; object-fit: cover; border-radius: 6px; background: #eee; flex-shrink: 0;
    }
    .header-search-results .hs-item-title { font-size: 0.88rem; font-weight: 500; }
    .header-search-results .hs-item-price { font-size: 0.78rem; color: #c41e3a; }
    .header-search-results .hs-empty,
    .header-search-results .hs-loading {
        padding: 20px 15px;
        text-align: center;
        color: #999;
        font-size: 0.85rem;
    }
</style>

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

// ===================== ค้นหาบนหัวเว็บ (Header Search) =====================
function hsEscapeHtml(str) {
    const div = document.createElement('div');
    div.innerText = str == null ? '' : String(str);
    return div.innerHTML;
}

function hsResultLink(item) {
    switch (item.module) {
        case 'product': return "{{ url('/products') }}/" + item.id;
        case 'reward': return "{{ url('/rewards-detail') }}/" + item.id;
        case 'insurance': return "{{ url('/insurance') }}/" + item.id;
        case 'locker': return "{{ route('lockers') }}";
        default: return '#';
    }
}

function renderHeaderSearchResults(data) {
    const box = document.getElementById('headerSearchResults');
    const groups = [
        { key: 'products_and_services', label: 'สินค้า/บริการ' },
        { key: 'lockers', label: 'ตู้เซฟนิรภัย' },
        { key: 'rewards', label: 'ของรางวัล' },
        { key: 'insurances', label: 'ประกันภัย' },
    ];
    let html = '';
    let hasAny = false;

    groups.forEach((g) => {
        const items = data[g.key] || [];
        if (items.length === 0) return;
        hasAny = true;
        html += `<div class="hs-group-label">${g.label}</div>`;
        items.forEach((item) => {
            let priceText = '';
            if (item.price !== null && item.price !== undefined) {
                priceText = Number(item.price).toLocaleString() + ' บาท';
            } else if (item.points_required) {
                priceText = Number(item.points_required).toLocaleString() + ' แต้ม';
            }
            html += `
                <a class="hs-item" href="${hsResultLink(item)}">
                    <img src="${item.image_url || ''}" onerror="this.style.display='none'">
                    <div>
                        <div class="hs-item-title">${hsEscapeHtml(item.title)}</div>
                        ${priceText ? `<div class="hs-item-price">${priceText}</div>` : ''}
                    </div>
                </a>`;
        });
    });

    box.innerHTML = hasAny ? html : '<div class="hs-empty">ไม่พบผลลัพธ์ที่ตรงกับคำค้นหา</div>';
}

function performHeaderSearch() {
    const input = document.getElementById('headerSearchInput');
    const box = document.getElementById('headerSearchResults');
    if (!input || !box) return;

    const keyword = input.value.trim();
    if (!keyword) {
        box.style.display = 'none';
        box.innerHTML = '';
        return;
    }

    box.style.display = 'block';
    box.innerHTML = '<div class="hs-loading">กำลังค้นหา...</div>';

    fetch('/api/search?q=' + encodeURIComponent(keyword))
        .then((res) => res.json())
        .then((json) => renderHeaderSearchResults(json.data || {}))
        .catch(() => { box.innerHTML = '<div class="hs-empty">เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง</div>'; });
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('headerSearchInput');
    const btn = document.getElementById('headerSearchBtn');

    if (btn) btn.addEventListener('click', function (e) { e.preventDefault(); performHeaderSearch(); });
    if (input) input.addEventListener('keypress', function (e) { if (e.key === 'Enter') { e.preventDefault(); performHeaderSearch(); } });

    // ปิดกล่องผลลัพธ์เมื่อคลิกนอกช่องค้นหา
    document.addEventListener('click', function (e) {
        const box = document.getElementById('headerSearchResults');
        const container = document.querySelector('.search-container');
        if (box && container && !container.contains(e.target)) {
            box.style.display = 'none';
        }
    });
});
</script>
