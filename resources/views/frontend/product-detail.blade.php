@extends('frontend.layouts.main')

@section('title', $product->name . ' - AEG EASE CLUB')

@section('styles')
    <style>
        :root {
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
        }
        .detail-section { padding: 60px 0 100px; background-color: #fff; }
        .detail-image-box { background: white; border: 1px solid #f2f2f2; border-radius: 25px; padding: 40px; width: 100%; max-width: 500px; aspect-ratio: 1 / 0.85; display: flex; align-items: center; justify-content: center; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02); }
        .detail-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .detail-content { padding-left: 30px; }
        .detail-title { color: var(--primary-dark); font-weight: 700; font-size: 1.8rem; margin-bottom: 10px; }
        .detail-subtitle { font-weight: 700; font-size: 1.4rem; color: var(--primary-red); margin-bottom: 10px; }
        .detail-desc { font-size: 0.95rem; color: #555; margin-bottom: 20px; line-height: 1.6; }
        .btn-container-centered { display: flex; justify-content: flex-start; gap: 20px; margin-top: 30px; width: 100%; }
        .btn-gradient { background: var(--btn-gradient); color: white !important; border: none; border-radius: 50px; padding: 12px 0; width: 220px; font-size: 1.1rem; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2); transition: all 0.3s ease; cursor: pointer; }
        .btn-gradient:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3); opacity: 0.95; }
        .btn-outline-dark { border-radius: 50px; padding: 12px 0; width: 220px; font-size: 1.1rem; font-weight: 600; }
        
        @media (max-width: 992px) {
            .detail-content { padding-left: 0; margin-top: 30px; text-align: center; }
            .btn-container-centered { flex-direction: column; align-items: center; gap: 15px; margin-top: 40px; }
            .btn-gradient, .btn-outline-dark { width: 100%; max-width: 300px; }
            .qty-container { justify-content: center; }
        }
    </style>
@endsection

@section('content')
    <main class="detail-section">
        <div class="container">
            <div class="row align-items-center">
                
                <div class="col-lg-6 d-flex justify-content-center">
                    <div class="detail-image-box">
                        <img src="{{ $product->image_url ?? asset('assets/image/product-1.webp') }}" alt="{{ $product->name }}">
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="detail-content">
                        <h1 class="detail-title">{{ $product->name }}</h1>
                        <h2 class="detail-subtitle">฿{{ number_format($product->price, 2) }}</h2>
                        <p class="detail-desc">{{ $product->description ?? 'ยังไม่มีรายละเอียดสินค้า' }}</p>

                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            <div class="d-flex align-items-center gap-3 mb-4 qty-container">
                                <label for="quantity" class="fw-bold text-secondary">จำนวน :</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" class="form-control text-center" style="width: 100px; border-radius: 10px;">
                            </div>

                            <div class="btn-container-centered">
                                <a href="{{ route('product-categories') }}" class="btn btn-outline-dark">ย้อนกลับ</a>
                                <button type="submit" class="btn-gradient">
                                    <i class="fas fa-shopping-cart me-2"></i> หยิบใส่ตะกร้า
                                </button>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection