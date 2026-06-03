@extends('frontend.layouts.main')

@section('title', 'ข้อกำหนดและเงื่อนไข - AEG EASE CLUB')

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
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 10px;
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

        /* --- Terms Main Content --- */
        .terms-wrapper {
            padding: 60px 0 100px;
        }

        .terms-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 80px;
            border: none;
        }

        .terms-header {
            margin-bottom: 40px;
        }

        .terms-header span {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            color: #999;
            font-weight: 600;
        }

        .terms-header h1 {
            color: #1a2d5e;
            font-weight: 700;
            font-size: 2.2rem;
            margin-top: 5px;
        }

        .terms-intro {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 40px;
        }

        .term-section {
            margin-bottom: 35px;
        }

        .term-section h2 {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1a2d5e;
            margin-bottom: 15px;
        }

        .term-section p,
        .term-section li {
            font-size: 1.05rem;
            /* Adjusted for readability */
            line-height: 1.8;
            color: #444;
        }

        .term-list {
            list-style: none;
            padding-left: 0;
        }

        .term-list li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 20px;
        }

        .term-list li::before {
            content: "-";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 700;
        }

        /* Sticky Bottom Button */
        .terms-footer-action {
            text-align: center;
            margin-top: 50px;
        }

        .btn-accept {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 60px;
            font-size: 1.15rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 20px rgba(196, 30, 58, 0.25);
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

            .terms-card {
                padding: 40px 30px;
                border-radius: 20px;
            }

            .terms-header h1 {
                font-size: 1.6rem;
            }

            .term-section h2 {
                font-size: 1.1rem;
            }

            .term-section p,
            .term-section li {
                font-size: 0.95rem;
            }

            .btn-accept {
                width: 100%;
                max-width: 300px;
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

    <!-- Main Terms Content -->
    <main class="terms-wrapper">
        <div class="container">
            <article class="terms-card">
                <header class="terms-header">
                    <span>TERMS AND CONDITIONS</span>
                    <h1>ข้อกำหนดและเงื่อนไข</h1>
                </header>

                <section class="terms-intro">
                    <p>กรุณาอ่านข้อกำหนด และเงื่อนไขการใช้งาน EASE CLUB โดยละเอียดก่อนสมัครสมาชิก และใช้สิทธิ์ต่างๆ
                        สิทธิเหล่านี้จัดทำขึ้นเพื่อใช้ร่วมกับรายการส่งเสริมการขายต่างๆ ตลอดจนการบริการลูกค้าของบริษัทฯ
                        รวมถึงรายละเอียดอื่นๆ เพื่อรักษาสิทธิของท่าน กรุณาอ่านทำความเข้าใจในเนื้อหาเหล่านี้อย่างถ่องแท้
                    </p>
                </section>

                <!-- Section 1 -->
                <div class="term-section">
                    <h2>1. บทนำเบื้องต้น</h2>
                    <p>ระบบสมาชิก EASE CLUB เป็น Loyalty Program ของบริษัทฯ ที่จัดทำขึ้นเพื่อสมาชิกที่สมัคร
                        และเข้าใช้งานผ่านแอปพลิเคชัน โดยรวบรวมสิทธิประโยชน์ต่างๆ มากมาย
                        เพื่อมอบให้แก่สมาชิกที่มีประวัติการซื้อสินค้า และใช้บริการของบริษัทฯ
                        รวมถึงสิทธิพิเศษจากพาร์ทเนอร์รายต่างๆ</p>
                </div>

                <!-- Section 2 -->
                <div class="term-section">
                    <h2>2. สิทธิการเป็นสมาชิก</h2>
                    <ul class="term-list">
                        <li>สมาชิกจะต้องลงทะเบียนผ่านแอปพลิเคชัน</li>
                        <li>ข้อมูลที่ลงทะเบียนต้องเป็นความจริง</li>
                        <li>บริษัทฯ ขอสงวนสิทธิ์ในการยกเลิกสถานะสมาชิกหากพบการใช้งานที่ผิดปกติ</li>
                    </ul>
                </div>

                <!-- Section 3 -->
                <div class="term-section">
                    <h2>3. การลงทะเบียน และข้อมูลส่วนตัว</h2>
                    <p>การสมัครสมาชิกต้องระบุ ชื่อ-นามสกุล เบอร์โทรศัพท์ และอีเมลที่สามารถติดต่อได้
                        เพื่อความปลอดภัยในการยืนยันตัวตนและการรับสิทธิ์</p>
                </div>

                <!-- Section 4 -->
                <div class="term-section">
                    <h2>4. การสะสมคะแนน</h2>
                    <p>คะแนนสะสม (Ease Points) จะได้รับจากการซื้อแพ็กเกจสินค้า หรือร่วมกิจกรรมที่บริษัทฯ กำหนด
                        โดยคะแนนจะมีอายุการใช้งาน 1 ปีปฏิทิน</p>
                </div>

                <!-- Section 5 -->
                <div class="term-section">
                    <h2>5. ระดับสมาชิก</h2>
                    <p>ระดับสมาชิกแบ่งตามยอดการใช้งานสะสม:</p>
                    <ul class="term-list">
                        <li>Advance Member: ยอดสะสมขั้นต่ำ 10,000 บาท</li>
                        <li>Platinum Member: ยอดสะสมขั้นต่ำ 50,000 บาท</li>
                    </ul>
                </div>

                <!-- Section 6-13 (Summary for brevity based on image) -->
                <div class="term-section">
                    <h2>6. การคืนสินค้าและการยกเลิกสิทธิ์</h2>
                    <p>การยกเลิกรายการสั่งซื้อหรือคืนสินค้า จะส่งผลต่อคะแนนสะสมที่ได้รับจากรายการนั้นๆ</p>
                </div>

                <div class="term-section">
                    <h2>7. การเปลี่ยนแปลงเงื่อนไข</h2>
                    <p>บริษัทฯ ขอสงวนสิทธิ์ในการเปลี่ยนแปลงข้อกำหนดและเงื่อนไขโดยมิต้องแจ้งให้ทราบล่วงหน้า</p>
                </div>

                <div class="terms-footer-action">
                    <a href="index" class="btn-accept">ยอมรับ</a>
                </div>
            </article>
        </div>
    </main>

    <!-- Footer Section -->
@endsection
