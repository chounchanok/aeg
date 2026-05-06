<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container navbar-container">
        <!-- Top Row -->
        <div class="navbar-top-row w-100">
            <div class="nav-icons ms-auto">
                <a href="{{ route('repair-history') }}" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                <a href="{{ route('repair-status') }}" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                <a href="{{ route('my-account') }}" class="nav-icon-item"><i class="fas fa-user"></i><span>ข้อมูลของฉัน</span></a>
                <span style="color: rgba(255,255,255,0.5);">|</span>
                <div class="lang-selector">
                    <img src="https://flagcdn.com/w20/th.png" alt="TH Flag" width="20">
                    <span>TH</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="navbar-bottom-row w-100">
            <a class="navbar-brand" href="index">
                <img src="assets/image/logo.webp" alt="AEG Logo"
                    onerror="this.src='https://via.placeholder.com/150x50?text=AEG+LOGO'">
            </a>

            <div class="search-container mx-auto">
                <input type="text" class="search-input" placeholder="ค้นหา">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>

            <div class="cart-section">
                <a href="cart" class="cart-icon" style="color: white; text-decoration: none;"><i
                        class="fas fa-shopping-cart"></i></a>
                <div class="points-badge">
                    <i class="fas fa-coins" style="color: #f1c40f;"></i> 200
                </div>
            </div>
        </div>
    </div>
</nav>
