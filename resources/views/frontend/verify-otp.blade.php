@extends('frontend.layouts.main')

@section('title', __('ยืนยันรหัส OTP') . ' - AEG EASE CLUB')

@push('styles')
<style>
    :root {
        --primary-dark: #1a1a2e;
        --primary-red: #c41e3a;
        --primary-purple: #4a1c40;
        --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
    }

    body {
        font-family: 'Poppins', 'Kanit', sans-serif !important;
        background-color: #f8f9fa; /* พื้นหลังสีอ่อนเพื่อให้ Card โดดเด่นขึ้น */
        color: #333;
    }

    /* --- Auth / OTP Section Styles --- */
    .auth-section {
        padding: 80px 0 100px;
        min-height: 75vh;
        display: flex;
        align-items: center;
    }

    .auth-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        padding: 40px 30px;
        border: none;
    }

    .auth-title {
        color: var(--primary-dark);
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 10px;
        text-align: center;
    }

    .auth-subtitle {
        color: #666;
        text-align: center;
        font-size: 0.95rem;
        margin-bottom: 30px;
        line-height: 1.5;
    }

    .form-control-otp {
        border-radius: 15px;
        padding: 15px 20px;
        background-color: #f1f3f8;
        border: 1px solid transparent;
        text-align: center;
        font-size: 1.8rem;
        letter-spacing: 8px;
        font-weight: 600;
        color: var(--primary-dark);
        transition: all 0.3s;
    }

    .form-control-otp:focus {
        background-color: #ffffff;
        border-color: var(--primary-red);
        box-shadow: 0 0 0 4px rgba(196, 30, 58, 0.1);
        outline: none;
    }

    .form-control-otp::placeholder {
        color: #ccc;
        font-weight: 400;
        letter-spacing: 5px;
    }

    .btn-auth {
        background: var(--btn-gradient);
        color: white !important;
        border: none;
        border-radius: 50px;
        padding: 14px 0;
        font-size: 1.1rem;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
    }

    .btn-auth:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
        opacity: 0.95;
    }

    .resend-link {
        color: var(--primary-red);
        text-decoration: none;
        font-weight: 600;
        transition: opacity 0.3s;
    }

    .resend-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    /* ซ่อนลูกศรขึ้นลงของช่อง input type number */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@section('content')
    <main class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5">
                    
                    <div class="auth-card">
                        <div class="text-center mb-4">
                            <i class="fas fa-mobile-screen-button" style="font-size: 3.5rem; color: var(--primary-dark);"></i>
                        </div>
                        
                        <h2 class="auth-title">{{ __('ยืนยันรหัส OTP') }}</h2>
                        <!-- 🌟 เปลี่ยนให้เป็นแบบนี้ครับ -->
                        <p class="auth-subtitle">
                            {{ __('กรุณากรอกรหัส OTP 6 หลัก') }} <br>
                            {{ __('ที่เราได้ส่งไปยังเบอร์') }} <span class="fw-bold text-dark">{{ substr_replace($phone, 'XXXX', 3, 4) }}</span><br>
                            <span class="badge bg-light text-secondary border mt-2 px-3 py-2" style="font-size: 0.9rem;">
                                Ref Code: <span class="text-danger fw-bold">{{ $refCode ?? 'N/A' }}</span>
                            </span>
                        </p>

                        @if(session('error'))
                            <div class="alert alert-danger text-center" style="border-radius: 12px; font-size: 0.9rem;">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('verify-otp.submit') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <input type="number" 
                                       name="otp" 
                                       class="form-control form-control-otp @error('otp') is-invalid @enderror" 
                                       placeholder="------" 
                                       required 
                                       autofocus 
                                       oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6);">
                                
                                @error('otp')
                                    <div class="invalid-feedback text-center mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-auth mb-4">
                                {{ __('ยืนยันรหัส OTP') }}
                            </button>

                            <div class="text-center">
                                <span class="text-muted" style="font-size: 0.95rem;">{{ __('ยังไม่ได้รับรหัส?') }}</span>
                                <a href="#" class="resend-link ms-1">{{ __('ส่งรหัสใหม่อีกครั้ง') }}</a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>
@endsection