@extends('frontend.layouts.main')

@section('title', 'ระบบรักษาความปลอดภัย - AEG EASE CLUB')

@section('styles')
    <style>
        /* สไตล์เฉพาะของหน้านี้ (ยกมาจากที่คุณเขียนไว้) */
        :root {
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
        }
        .category-nav { padding: 50px 0; background: white; border-bottom: 1px solid #f0f0f0; }
        .nav-tabs-custom { display: flex; justify-content: center; gap: 20px; border: none; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 15px; scrollbar-width: none; }
        .nav-tabs-custom::-webkit-scrollbar { display: none; }
        .nav-tabs-custom .nav-link { border: none !important; padding: 0; background: none !important; }
        .category-box { background: #ffffff; border: 1px solid #f0f0f0; border-radius: 20px; padding: 25px 15px; width: 220px; height: 190px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; transition: all 0.3s ease; }
        .category-icon { width: 70px; height: 70px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; }
        .category-icon img { max-width: 100%; max-height: 100%; object-fit: contain; filter: grayscale(100%); opacity: 0.4; transition: all 0.3s ease; }
        .category-text-en { font-weight: 700; font-size: 0.95rem; margin-bottom: 4px; color: #bbb; }
        .category-text-th { font-weight: 400; font-size: 0.85rem; color: #ccc; }
        .nav-link.active .category-box { border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06); background-color: #fff; transform: translateY(-5px); }
        .nav-link.active .category-icon img { filter: grayscale(0%); opacity: 1; }
        .nav-link.active .category-text-en { color: #111; }
        .nav-link.active .category-text-th { color: #444; }
        .product-section { padding: 60px 0 100px; background-color: #fff; }
        .product-item-wrapper { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 60px; }
        .product-image-frame { background: white; border: 1px solid #f2f2f2; border-radius: 25px; padding: 30px; width: 100%; max-width: 380px; aspect-ratio: 1 / 0.8; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; transition: all 0.3s ease; }
        .product-image-frame:hover { box-shadow: 0 15px 40px rgba(0, 0, 0, 0.07); border-color: #e8e8e8; }
        .product-image-frame img { max-width: 95%; max-height: 95%; object-fit: contain; }
        .product-title-en { font-weight: 700; font-size: 1.15rem; margin-bottom: 4px; color: #1a2d5e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
        .product-title-th { font-weight: 500; font-size: 1rem; color: #c41e3a; margin-bottom: 20px; } /* ปรับสีราคาเป็นสีแดง */
        .btn-action-sales { background: var(--btn-gradient); color: white !important; border: none; border-radius: 50px; padding: 10px 50px; font-size: 1.1rem; font-weight: 600; text-decoration: none; display: inline-block; box-shadow: 0 5px 15px rgba(196, 30, 58, 0.25); transition: all 0.3s ease; }
        .btn-action-sales:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(196, 30, 58, 0.35); opacity: 0.95; }
        @media (max-width: 1200px) { .category-box { width: 180px; height: 160px; } .product-image-frame { max-width: 320px; } }
        @media (max-width: 992px) { .nav-tabs-custom { justify-content: flex-start; padding-left: 15px; } .product-image-frame { max-width: 100%; padding: 20px; } .btn-action-sales { font-size: 1rem; padding: 8px 35px; } .category-box { width: 150px; height: 150px; } .category-icon { width: 60px; height: 60px; } }
    </style>
@endsection

@section('content')
    <div class="category-nav">
        <div class="container">
            <div class="nav nav-tabs nav-tabs-custom" id="securityTabs" role="tablist">
                <button class="nav-link active" type="button">
                    <div class="category-box">
                        <div class="category-icon"><img src="{{ asset('assets/image/cat1.webp') }}" alt="Category"></div>
                        <div class="category-text-en">All Products</div>
                        <div class="category-text-th">สินค้าทั้งหมด</div>
                    </div>
                </button>
                </div>
        </div>
    </div>

    <main class="product-section">
        <div class="container">
            <div class="tab-content">
                <div class="tab-pane fade show active" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        
                        @forelse($products as $product)
                            <div class="col-6 col-md-4">
                                <div class="product-item-wrapper">
                                    <a href="{{ route('product-detail', $product->id) }}" class="text-decoration-none w-100 d-flex justify-content-center">
                                        <div class="product-image-frame">
                                            <img src="{{ $product->image_url ?? asset('assets/image/product-1.webp') }}" alt="{{ $product->name }}">
                                        </div>
                                    </a>
                                    <div class="product-title-en">{{ $product->name }}</div>
                                    <div class="product-title-th">฿{{ number_format($product->price, 2) }}</div>
                                    
                                    <a href="{{ route('product-detail', $product->id) }}" class="btn-action-sales">ดูรายละเอียด</a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted">
                                <h5>ไม่พบข้อมูลสินค้า</h5>
                            </div>
                        @endforelse

                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </main>

    <div class="position-fixed" style="bottom: 40px; right: 40px; z-index: 1000;">
        <div class="bg-white rounded-circle shadow d-flex align-items-center justify-content-center border" style="width: 65px; height: 65px;">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>
@endsection