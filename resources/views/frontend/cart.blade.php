@extends('frontend.layouts.main')

@section('title', 'ตะกร้าสินค้า - AEG EASE CLUB')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-purple: #4a1c40;
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles --- */
        .navbar {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
            background-color: var(--primary-dark);
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .navbar-top-row {
            display: flex;
            justify-content: flex-end;
            padding-bottom: 0px !important;
            border-bottom: 0px solid rgba(255, 255, 255, 0.2) !important;
            margin-bottom: 0px !important;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon-item {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }

        .navbar-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand img {
            height: 50px;
        }

        .search-container {
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .search-input {
            border-radius: 25px;
            padding: 10px 50px 10px 20px;
            border: none;
            width: 100%;
            font-size: 1rem;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #666;
            border: none;
            font-size: 1.2rem;
        }

        /* --- Custom Radio Point Design --- */
        .custom-radio {
            cursor: pointer;
            width: 18px;
            height: 18px;
            background-color: #ddd;
            border-radius: 50%;
            display: inline-block;
            position: relative;
            transition: 0.3s;
        }

        .radio-active {
            background-color: var(--primary-red) !important;
        }

        /* --- Main Cart Content --- */
        .cart-main {
            padding: 60px 0 100px;
            min-height: 70vh;
        }

        .cart-header {
            display: flex;
            align-items: center;
            gap: 15px;
            max-width: 800px;
            margin: 0 auto 30px;
            padding: 0 15px;
            cursor: pointer;
        }

        .select-all-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        /* Cart Item Cards */
        .cart-item-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 20px;
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: 0.3s;
            cursor: pointer;
        }

        .cart-item-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .item-info-group {
            display: flex;
            align-items: center;
            gap: 25px;
            flex-grow: 1;
        }

        .item-image-box {
            width: 180px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            background: #fcfcfc;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f0f0f0;
        }

        .item-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            text-align: left;
        }

        .item-title {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .item-subtitle {
            font-size: 0.95rem;
            color: #777;
            margin-bottom: 0;
        }

        /* Action Buttons */
        .cart-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 60px;
        }

        .btn-cart {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 45px;
            font-size: 1.1rem;
            font-weight: 600;
            min-width: 220px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: 0.3s;
        }

        .btn-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
        }

        /* --- QR Code Modal Styles (Exact match to QR Code.png) --- */
        .modal-qr-content {
            border-radius: 20px !important;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        }

        /* ส่วนหัวและส่วนแสดง QR เป็นสีน้ำเงินกรมท่าเดียวกัน */
        .modal-qr-blue-section {
            background-color: var(--primary-dark);
            color: white;
            padding: 25px 20px 40px;
            text-align: center;
            position: relative;
        }

        .modal-qr-title {
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }

        .btn-qr-close {
            position: absolute;
            right: 20px;
            top: 20px;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.2rem;
            line-height: 1;
            cursor: pointer;
            transition: 0.2s;
            opacity: 0.8;
            z-index: 10;
        }

        .btn-qr-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .qr-image-container {
            display: inline-block;
            background: white;
            padding: 12px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .qr-image-container img {
            width: 150px;
            height: 150px;
            display: block;
        }

        /* ส่วนท้ายเป็นสีขาว */
        .qr-footer-info {
            background-color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .qr-instruction {
            font-size: 0.95rem;
            color: #333;
            margin: 0;
            font-weight: 400;
            letter-spacing: 0.2px;
        }

        /* Floating Icon */
        .floating-icon {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .chat-btn {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f0f0f0;
        }

        /* --- Footer Styles --- */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .footer-column {
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0 25px;
        }

        .social-icons-bar {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .social-icons-bar a {
            color: white;
            margin: 0 15px;
            font-size: 1.3rem;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .item-info-group {
                gap: 15px;
            }

            .item-image-box {
                width: 120px;
                height: 90px;
            }

            .item-title {
                font-size: 1.1rem;
            }

            .cart-actions {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn-cart {
                width: 100%;
                max-width: 320px;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                margin-bottom: 25px;
                padding-bottom: 25px;
            }
        }
    </style>
@endpush

@section('content')

    <main class="cart-main">
        <div class="container">

            @if(isset($cartItems) && $cartItems->count() > 0)
                <div class="cart-header" onclick="toggleAll()">
                    <span class="custom-radio radio-active" id="allSelector"></span>
                    <span class="select-all-text">เลือกทั้งหมด</span>
                </div>

                @foreach($cartItems as $item)
                    <div class="cart-item-card" onclick="toggleItem({{ $item->cart_item_id }})">
                        <div class="item-info-group">
                            <div class="item-image-box">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                    onerror="this.src='https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=300&q=80'">
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">{{ $item->name }}</h3>
                                <!-- 🌟 ให้แก้เป็นแบบนี้ครับ -->
                                <p class="text-muted mb-2" style="font-size: 0.9rem;">
                                    จำนวน: {{ $item->quantity }} ชิ้น 
                                    @if($item->duration_months > 1)
                                        <span class="text-danger">| ระยะเวลา: {{ $item->duration_months }} เดือน</span> 
                                    @endif
                                    | ราคา: {{ number_format($item->price, 2) }} ฿
                                </p>

                                <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST" class="mt-2" onclick="event.stopPropagation();">
                                    @csrf
                                    <button type="submit" class="text-danger border-0 bg-transparent p-0 btn-sm" style="font-size: 13px;">
                                        <i class="fas fa-trash-can mr-1"></i> ลบรายการนี้
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="custom-radio radio-active item-radio" id="itemRadio-{{ $item->cart_item_id }}" data-item-total="{{ $item->price * $item->quantity }}"></div>
                    </div>
                @endforeach

                <div class="text-end my-4 px-2">
                    <h4 class="fw-bold text-dark" style="font-size: 1.1rem;">ยอดรวมทั้งหมด: <span class="text-danger" id="totalAmountDisplay">{{ number_format($totalAmount, 2) }} ฿</span></h4>
                </div>

                <div class="cart-actions">
                    <button type="button" onclick="history.back();" class="btn-cart text-center text-dark d-flex align-items-center justify-content-center" style="background: white; border: 1px solid #ddd; text-decoration: none; color: #333 !important;">ย้อนกลับ</button>
                    <button class="btn-cart" data-bs-toggle="modal" data-bs-target="#qrModal">ดำเนินการต่อ</button>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">ตะกร้าสินค้าของคุณยังว่างเปล่า</h4>
                    <a href="{{ route('packages', 'equipment') }}" class="btn btn-cart mt-4 d-inline-flex align-items-center justify-content-center text-white" style="text-decoration: none; min-width: 200px;">ไปเลือกซื้อสินค้ากันเลย!</a>
                </div>
            @endif

        </div>
    </main>

    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content modal-qr-content">
                <div class="modal-qr-blue-section">
                    <h5 class="modal-qr-title">สแกน Qr Code</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <div class="qr-image-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{  urlencode(route('app.download')) }}"
                            alt="QR Code">
                    </div>
                </div>
                <div class="qr-footer-info">
                    <p class="qr-instruction">สแกนคิวอาร์โค้ดเพื่อดำเนินการต่อผ่านแอปพลิเคชัน</p>
                </div>
            </div>
        </div>
    </div>

    @if(!Auth::user())
    <div class="floating-icon">
        <div class="chat-btn">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>
    @endif

@endsection

<!-- แทรก Script เฉพาะหน้าลงท้ายไฟล์ Layout -->
@push('scripts')
<script>
    function toggleItem(id) {
        const radio = document.getElementById('itemRadio-' + id);
        radio.classList.toggle('radio-active');
        updateSelectAllState();
        calculateTotal(); // 🌟 สั่งให้คำนวณยอดใหม่ทุกครั้งที่กด
    }

    function toggleAll() {
        const allRadio = document.getElementById('allSelector');
        const itemRadios = document.querySelectorAll('.item-radio');
        const isCurrentlyActive = allRadio.classList.contains('radio-active');

        if (isCurrentlyActive) {
            allRadio.classList.remove('radio-active');
            itemRadios.forEach(r => r.classList.remove('radio-active'));
        } else {
            allRadio.classList.add('radio-active');
            itemRadios.forEach(r => r.classList.add('radio-active'));
        }
        calculateTotal(); // 🌟 สั่งให้คำนวณยอดใหม่ทุกครั้งที่กดเลือกทั้งหมด
    }

    function updateSelectAllState() {
        const allRadio = document.getElementById('allSelector');
        const itemRadios = document.querySelectorAll('.item-radio');
        const activeRadios = document.querySelectorAll('.item-radio.radio-active');

        if (activeRadios.length === itemRadios.length && itemRadios.length > 0) {
            allRadio.classList.add('radio-active');
        } else {
            allRadio.classList.remove('radio-active');
        }
    }

    // 🌟 ฟังก์ชันใหม่: คำนวณยอดรวมจากรายการที่ถูกเลือก
    function calculateTotal() {
        // ดึงเฉพาะปุ่มที่มีคลาส radio-active (ถูกเลือกอยู่)
        const activeRadios = document.querySelectorAll('.item-radio.radio-active');
        let total = 0;

        // วนลูปบวกเลขจาก attribute data-item-total
        activeRadios.forEach(radio => {
            total += parseFloat(radio.getAttribute('data-item-total'));
        });

        // แปลงตัวเลขให้มีลูกน้ำ (คอมม่า) และทศนิยม 2 ตำแหน่ง
        const formattedTotal = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // ส่งตัวเลขกลับไปแสดงที่หน้าจอ
        document.getElementById('totalAmountDisplay').innerText = formattedTotal + ' ฿';
    }
</script>
@endpush
