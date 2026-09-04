@extends('frontend.layouts.main')

@section('title', __('การลบบัญชี') . ' - AEG EASE CLUB')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --btn-gradient: linear-gradient(90deg, #1a2d5e 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
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
            font-size: 0.9rem;
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

        /* --- Policy Main Content --- */
        .policy-wrapper {
            padding: 60px 0 100px;
        }

        .policy-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 80px;
            border: none;
        }

        .policy-header {
            margin-bottom: 40px;
        }

        .policy-header span {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            color: #999;
            font-weight: 600;
        }

        .policy-header h1 {
            color: #1a2d5e;
            font-weight: 700;
            font-size: 2.2rem;
            margin-top: 5px;
        }

        .policy-intro {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 40px;
        }

        .policy-section {
            margin-bottom: 35px;
        }

        .policy-section h2 {
            font-weight: 700;
            font-size: 1.3rem;
            color: #1a2d5e;
            margin-bottom: 15px;
        }

        .policy-section h3 {
            font-weight: 600;
            font-size: 1.08rem;
            color: #1a2d5e;
            margin: 20px 0 10px;
        }

        .policy-section p,
        .policy-section li {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #444;
        }

        .policy-section p+p {
            margin-top: -8px;
        }

        .policy-list {
            list-style: none;
            padding-left: 0;
        }

        .policy-list li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
        }

        .policy-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .policy-list-numbered {
            list-style: none;
            padding-left: 0;
            counter-reset: step-counter;
        }

        .policy-list-numbered li {
            margin-bottom: 12px;
            position: relative;
            padding-left: 40px;
            counter-increment: step-counter;
        }

        .policy-list-numbered li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            width: 26px;
            height: 26px;
            line-height: 26px;
            text-align: center;
            border-radius: 50%;
            background: var(--btn-gradient);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .policy-note {
            background: #f8f9fb;
            border-left: 3px solid var(--primary-red);
            border-radius: 8px;
            padding: 15px 20px;
            font-size: 0.95rem !important;
            color: #555 !important;
            margin-top: 15px;
        }

        /* --- Footer Styles --- */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 40px 0 20px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .footer-column {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding-right: 20px;
            padding-left: 20px;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .policy-card {
                padding: 40px 30px;
                border-radius: 20px;
            }

            .policy-header h1 {
                font-size: 1.6rem;
            }

            .policy-section h2 {
                font-size: 1.15rem;
            }

            .policy-section h3 {
                font-size: 1rem;
            }

            .policy-section p,
            .policy-section li {
                font-size: 0.95rem;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                margin-bottom: 20px;
                padding-bottom: 20px;
            }
        }
    </style>
@endpush

@section('content')

    <!-- Main Content -->
    <main class="policy-wrapper">
        <div class="container">
            <article class="policy-card">
                <header class="policy-header">
                    <span>ACCOUNT DELETION</span>
                    <h1>{{ __('การลบบัญชี AEG EASE CLUB') }}</h1>
                </header>

                <section class="policy-intro">
                    <p>{{ __('หน้านี้อธิบายวิธีที่ผู้ใช้งานแอปพลิเคชันและเว็บไซต์ AEG EASE CLUB สามารถขอลบบัญชีผู้ใช้ และข้อมูลส่วนบุคคลที่เกี่ยวข้องได้') }}</p>
                </section>

                <div class="policy-section">
                    <h2>1. {{ __('วิธีลบบัญชีในแอป AEG EASE CLUB') }}</h2>
                    <ol class="policy-list-numbered">
                        <li>{{ __('เปิดแอปพลิเคชัน AEG EASE CLUB') }}</li>
                        <li>{{ __('ไปที่เมนู “ข้อมูลของฉัน” หรือ “การตั้งค่า”') }}</li>
                        <li>{{ __('เลือก “ลบบัญชี”') }}</li>
                        <li>{{ __('ยืนยันคำขอลบบัญชี') }}</li>
                    </ol>
                    <p>{{ __('เมื่อท่านยืนยันคำขอแล้ว ระบบจะดำเนินการลบบัญชีตามคำขอของท่าน') }}</p>
                </div>

                <div class="policy-section">
                    <h2>2. {{ __('ข้อมูลที่จะถูกลบ') }}</h2>
                    <p>{{ __('เมื่อบัญชีของท่านถูกลบ เราจะลบหรือตัดการเชื่อมโยงข้อมูลส่วนบุคคลที่เกี่ยวข้องกับบัญชีของท่าน ซึ่งรวมถึง') }}</p>
                    <ul class="policy-list">
                        <li>{{ __('ข้อมูลโปรไฟล์บัญชี เช่น ชื่อ-นามสกุล อีเมล เบอร์โทรศัพท์ และที่อยู่') }}</li>
                        <li>{{ __('ข้อมูลสำหรับเข้าสู่ระบบและยืนยันตัวตน (เช่น รหัสผ่าน หรือการเชื่อมต่อบัญชีโซเชียลมีเดีย)') }}</li>
                        <li>{{ __('ข้อมูลการใช้งานแอปที่เชื่อมโยงกับบัญชี เช่น ประวัติการสั่งซื้อสินค้า/แพ็กเกจบริการ ประวัติการแจ้งซ่อม/นัดหมายช่าง รายการโปรด แต้มสะสมและคูปองของสมาชิก EASE CLUB (ตามความเกี่ยวข้อง)') }}</li>
                        <li>{{ __('Device Token / Notification Token ที่ใช้สำหรับส่งการแจ้งเตือนไปยังอุปกรณ์ของท่าน') }}</li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2>3. {{ __('ระยะเวลาในการเก็บรักษาข้อมูล') }}</h2>
                    <p>{{ __('ข้อมูลส่วนใหญ่จะถูกลบหลังจากคำขอลบบัญชีดำเนินการเสร็จสมบูรณ์ อย่างไรก็ตาม ข้อมูลบางส่วนอาจถูกเก็บรักษาไว้ต่อไปเท่าที่จำเป็น หากกฎหมายกำหนด หรือเพื่อวัตถุประสงค์ด้านความปลอดภัย การป้องกันการทุจริต การชำระเงิน ภาษี หรือบัญชี') }}</p>
                    <p class="policy-note">{{ __('หากยังไม่สามารถลบข้อมูลบางส่วนได้ทันทีเนื่องจากข้อจำกัดด้านการดำเนินงานหรือข้อกำหนดทางกฎหมาย เราจะดำเนินการลบหรือทำให้ข้อมูลไม่สามารถระบุตัวตนได้ทันทีที่ไม่มีความจำเป็นต้องเก็บรักษาข้อมูลนั้นอีกต่อไป') }}</p>
                </div>

                <div class="policy-section">
                    <h2>4. {{ __('การติดต่อ') }}</h2>
                    <p>{{ __('หากท่านไม่สามารถเข้าถึงแอปพลิเคชันได้ หรือต้องการความช่วยเหลือเกี่ยวกับการลบบัญชี สามารถติดต่อเราได้ที่อีเมล:') }}
                        <a href="mailto:app@aeginc.co">app@aeginc.co</a></p>
                </div>
            </article>
        </div>
    </main>

    <!-- Footer Section -->
@endsection
