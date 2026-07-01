@extends('frontend.layouts.main')

@section('title', 'นโยบายข้อมูลส่วนบุคคล - AEG EASE CLUB')

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
            /* Enlarged section headers */
            color: #1a2d5e;
            margin-bottom: 15px;
        }

        .policy-section p,
        .policy-section li {
            font-size: 1.05rem;
            /* Enlarged for readability */
            line-height: 1.8;
            color: #444;
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

        /* Accept Button */
        .policy-footer-action {
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
            transition: 0.3s;
        }

        .btn-accept:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(196, 30, 58, 0.35);
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

            .policy-section p,
            .policy-section li {
                font-size: 0.95rem;
            }

            .btn-accept {
                width: 100%;
                max-width: 300px;
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

    <!-- Main Policy Content -->
    <main class="policy-wrapper">
        <div class="container">
            <article class="policy-card">
                <header class="policy-header">
                    <span>PRIVACY POLICY</span>
                    <h1>นโยบายข้อมูลส่วนบุคคล</h1>
                </header>

                <section class="policy-intro">
                    <p>บริษัท เอ อี จี (ประเทศไทย) จำกัด ("บริษัทฯ")
                        ให้ความสำคัญอย่างยิ่งต่อการคุ้มครองข้อมูลส่วนบุคคลของท่าน
                        นโยบายฉบับนี้อธิบายถึงวิธีการที่เราเก็บรวบรวม ใช้ เปิดเผย
                        และรักษาความปลอดภัยข้อมูลส่วนบุคคลของสมาชิก EASE CLUB
                        เพื่อให้ท่านมั่นใจว่าข้อมูลของท่านจะได้รับความคุ้มครองตามกฎหมายคุ้มครองข้อมูลส่วนบุคคล (PDPA)
                    </p>
                </section>

                <div class="policy-section">
                    <h2>1. ข้อมูลที่เราเก็บรวบรวม</h2>
                    <p>เราเก็บรวบรวมข้อมูลที่จำเป็นเพื่อการให้บริการสมาชิก รวมถึงข้อมูลระบุตัวตน (ชื่อ-นามสกุล),
                        ข้อมูลติดต่อ (เบอร์โทรศัพท์, อีเมล, ที่อยู่),
                        และข้อมูลประวัติการทำรายการซื้อสินค้าหรือบริการผ่านระบบของเรา</p>
                </div>

                <div class="policy-section">
                    <h2>2. วิธีการเก็บรวบรวมข้อมูล</h2>
                    <p>บริษัทฯ เก็บข้อมูลผ่านการลงทะเบียนสมาชิกบนแอปพลิเคชัน การกรอกข้อมูลผ่านหน้าเว็บไซต์
                        และข้อมูลการสื่อสารต่างๆ เมื่อท่านติดต่อสอบถามเจ้าหน้าที่ผู้เชี่ยวชาญของเรา</p>
                </div>

                <div class="policy-section">
                    <h2>3. วัตถุประสงค์ในการประมวลผลข้อมูล</h2>
                    <ul class="policy-list">
                        <li>เพื่อบริหารจัดการระบบสมาชิก EASE CLUB และการสะสมคะแนน</li>
                        <li>เพื่อการให้บริการประกันภัยและการเช่าตู้เซฟที่ตรงตามความต้องการ</li>
                        <li>เพื่อสื่อสาร แจ้งข่าวสาร และนำเสนอสิทธิประโยชน์พิเศษ</li>
                        <li>เพื่อรักษาความมั่นคงปลอดภัยของบัญชีผู้ใช้งาน</li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2>4. การเปิดเผยข้อมูล</h2>
                    <p>เราจะไม่เปิดเผยข้อมูลส่วนบุคคลของท่านให้แก่บุคคลภายนอก เว้นแต่จะเป็นการปฏิบัติตามกฎหมาย
                        หรือได้รับความยินยอมจากท่านล่วงหน้าเพื่อการร่วมรายการส่งเสริมการขายกับพาร์ทเนอร์ที่เกี่ยวข้อง
                    </p>
                </div>

                <div class="policy-section">
                    <h2>5. ระยะเวลาในการเก็บรักษาข้อมูล</h2>
                    <p>บริษัทฯ จะเก็บรักษาข้อมูลส่วนบุคคลของท่านไว้ตราบเท่าที่ท่านยังเป็นสมาชิกของ EASE CLUB
                        หรือตามระยะเวลาที่กฎหมายกำหนดเพื่อการตรวจสอบย้อนหลัง</p>
                </div>

                <div class="policy-section">
                    <h2>6. คุกกี้ (Cookies)</h2>
                    <p>เรามีการใช้งานคุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานเว็บไซต์และแอปพลิเคชันให้ดียิ่งขึ้น
                        โดยท่านสามารถเลือกตั้งค่าความเป็นส่วนตัวผ่านเบราว์เซอร์ของท่านได้</p>
                </div>

                <div class="policy-section">
                    <h2>7. สิทธิของเจ้าของข้อมูลส่วนบุคคล</h2>
                    <p>ท่านมีสิทธิ์ในการเข้าถึง ขอแก้ไข ขอระงับการใช้ หรือขอให้ลบข้อมูลส่วนบุคคลของท่าน
                        รวมถึงสิทธิ์ในการเพิกถอนความยินยอมในการประมวลผลข้อมูลผ่านช่องทางที่บริษัทฯ กำหนด</p>
                </div>

                <div class="policy-section">
                    <h2>8. มาตรการรักษาความมั่นคงปลอดภัย</h2>
                    <p>บริษัทฯ ใช้มาตรการรักษาความปลอดภัยที่ทันสมัยและเป็นมาตรฐานสากลเพื่อป้องกันการสูญหาย
                        การเข้าถึงโดยไม่ได้รับอนุญาต หรือการถูกโจรกรรมข้อมูล</p>
                </div>

                <div class="policy-section">
                    <h2>9. การโอนข้อมูลไปต่างประเทศ</h2>
                    <p>ในบางกรณีอาจมีการเก็บข้อมูลบนระบบ Cloud Server
                        ที่อยู่ต่างประเทศที่มีมาตรฐานการคุ้มครองข้อมูลที่เพียงพอตามข้อกำหนดของบริษัทฯ</p>
                </div>

                <div class="policy-section">
                    <h2>10. กฎหมายที่ใช้บังคับ</h2>
                    <p>นโยบายฉบับนี้ถูกร่างขึ้นและบังคับใช้ภายใต้กฎหมายของประเทศไทย
                        โดยเฉพาะพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562</p>
                </div>

                <div class="policy-section">
                    <h2>11. ความเป็นส่วนตัวเด็ก</h2>
                    <p>เราไม่เก็บข้อมูลส่วนบุคคลของบุคคลที่มีอายุต่ำกว่า 10 ปี
                        โดยไม่ได้รับความยินยอมจากผู้ปกครองตามเงื่อนไขทางกฎหมาย</p>
                </div>

                <div class="policy-section">
                    <h2>12. การเปลี่ยนแปลงนโยบาย</h2>
                    <p>บริษัทฯ ขอสงวนสิทธิ์ในการปรับปรุงนโยบายความเป็นส่วนตัวเพื่อให้สอดคล้องกับแนวทางปฏิบัติใหม่ๆ
                        โดยจะแจ้งการเปลี่ยนแปลงให้ทราบผ่านหน้าเว็บไซต์</p>
                </div>

                <div class="policy-section">
                    <h2>13. การติดต่อ</h2>
                    <p>หากท่านมีข้อสงสัยเกี่ยวกับนโยบายฉบับนี้ สามารถติดต่อเจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล (DPO)
                        ได้ที่สำนักงานใหญ่ของบริษัทฯ หรือผ่านทางอีเมล contact@aeginc.co</p>
                </div>

                <div class="policy-footer-action">
                    <a href="index" class="btn-accept">ยอมรับ</a>
                </div>
            </article>
        </div>
    </main>


    <!-- Footer Section -->
@endsection
