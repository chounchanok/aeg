@extends('frontend.layouts.main')

@section('title', 'ตะกร้าสินค้า - AEG EASE CLUB')

@section('content')

    <!-- Main Cart Content -->
    <main class="cart-main">
        <div class="container">

            @if(isset($cartItems) && $cartItems->items->count() > 0)
                <!-- Select All Header -->
                <div class="cart-header" onclick="toggleAll()">
                    <span class="custom-radio radio-active" id="allSelector"></span>
                    <span class="select-all-text">เลือกทั้งหมด</span>
                </div>

                <!-- Loop Cart Items -->
                @foreach($cartItems as $item)
                    <div class="cart-item">
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                        <h4>{{ $item->name }}</h4>
                        <p>ราคา: {{ number_format($item->price, 2) }} ฿</p>
                        <p>จำนวน: {{ $item->quantity }}</p>
                        
                        <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                        </form>
                    </div>
                @endforeach

                <div class="total">
                    <h3>ยอดรวมทั้งหมด: {{ number_format($totalAmount, 2) }} ฿</h3>
                </div>

                <!-- Action Buttons -->
                <div class="cart-actions">
                    <a href="{{ route('packages', 'equipment') }}" class="btn-cart text-dark" style="background: white; border: 1px solid #ddd;">ซื้อสินค้าเพิ่ม</a>
                    <button class="btn-cart" data-bs-toggle="modal" data-bs-target="#qrModal">ดำเนินการต่อ (ชำระเงิน)</button>
                </div>
            @else
                <!-- กรณีไม่มีสินค้าในตะกร้า -->
                <div class="text-center py-5">
                    <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">ตะกร้าสินค้าของคุณยังว่างเปล่า</h4>
                    <a href="{{ route('packages', 'equipment') }}" class="btn btn-cart mt-4">ไปเลือกซื้อสินค้ากันเลย!</a>
                </div>
            @endif

        </div>
    </main>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content modal-qr-content">
                <div class="modal-qr-blue-section">
                    <h5 class="modal-qr-title">สแกน Qr Code</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal"><i class="fas fa-xmark"></i></button>
                    <div class="qr-image-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=AEG-Checkout-Proceed" alt="QR Code">
                    </div>
                </div>
                <div class="qr-footer-info">
                    <p class="qr-instruction">สแกนคิวอาร์โค้ดเพื่อดำเนินการต่อผ่านแอปพลิเคชัน</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat Icon -->
    <div class="floating-icon">
        <div class="chat-btn">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>

@endsection

<!-- แทรก Script เฉพาะหน้าลงท้ายไฟล์ Layout -->
@push('scripts')
<script>
    function toggleItem(id) {
        const radio = document.getElementById('itemRadio-' + id);
        radio.classList.toggle('radio-active');
        updateSelectAllState();
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
</script>
@endpush