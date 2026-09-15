@extends('frontend.layouts.main')

@section('title', __('นโยบายข้อมูลส่วนบุคคล') . ' - AEG EASE CLUB')

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

        .policy-section h3 {
            font-weight: 600;
            font-size: 1.08rem;
            color: #1a2d5e;
            margin: 20px 0 10px;
        }

        .policy-section p,
        .policy-section li {
            font-size: 1.05rem;
            /* Enlarged for readability */
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

        .policy-note {
            background: #f8f9fb;
            border-left: 3px solid var(--primary-red);
            border-radius: 8px;
            padding: 15px 20px;
            font-size: 0.95rem !important;
            color: #555 !important;
            margin-top: 15px;
        }

        .policy-sub {
            margin-left: 20px;
        }

        .policy-subsub {
            margin-left: 40px;
        }

        /* --- Permissions Table --- */
        .policy-table-wrap {
            overflow-x: auto;
            margin: 15px 0;
        }

        table.policy-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        table.policy-table th,
        table.policy-table td {
            border: 1px solid #e5e7eb;
            padding: 12px 15px;
            text-align: left;
            vertical-align: top;
            line-height: 1.6;
            color: #444;
        }

        table.policy-table th {
            background: #1a2d5e;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
        }

        table.policy-table tr:nth-child(even) td {
            background: #f8f9fb;
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

            .policy-section h3 {
                font-size: 1rem;
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
                    <h1>{{ __('นโยบายข้อมูลส่วนบุคคล') }}</h1>
                </header>

                <section class="policy-intro">
                    <p>{{ __('กลุ่มบริษัทแองโกล อีสต์ กรุป จำกัด ("บริษัท") ได้จัดทำนโยบายส่วนบุคคล (Privacy Policy) นี้ โดยระบุถึงวิธีการที่บริษัทปฏิบัติต่อข้อมูลส่วนบุคคล เช่น การเก็บรวบรวม การจัดเก็บรักษา การใช้ การเปิดเผย รวมถึงสิทธิต่างๆ ของเจ้าของข้อมูล เป็นต้น เพื่อให้เจ้าของข้อมูลได้รับทราบถึงนโยบายในการคุ้มครองข้อมูลส่วนบุคคล บริษัทจึงประกาศนโยบายส่วนบุคคล ดังต่อไปนี้') }}</p>
                </section>

                <div class="policy-section">
                    <h2>{{ __('1. ข้อมูลส่วนบุคคล') }}</h2>
                    <p>{{ __('"ข้อมูลส่วนบุคคล" หมายถึง ข้อมูลที่สามารถระบุตัวตนของท่าน หรืออาจจะระบุตัวตนของท่านได้ไม่ว่าทางตรงหรือทางอ้อม') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('2. การเก็บรวบรวมข้อมูลส่วนบุคคลอย่างจำกัด') }}</h2>
                    <p class="policy-sub">{{ __('2.1 การจัดเก็บรวบรวมข้อมูลส่วนบุคคลจะกระทำโดยมีวัตถุประสงค์ ขอบเขต และใช้วิธีการที่ชอบด้วยกฎหมาย คำนึงถึงสิทธิมนุษยชนของเจ้าของข้อมูลส่วนบุคคล โดยที่ไม่ขัดแย้งกับกฎหมาย ในการเก็บรวบรวมและจัดเก็บข้อมูล ตลอดจนเก็บรวบรวมและจัดเก็บข้อมูลส่วนบุคคลอย่างจำกัดเพียงเท่าที่จำเป็นแก่การให้บริการ หรือบริการด้วยวิธีการทางอิเล็กทรอนิกส์อื่นใดภายใต้วัตถุประสงค์ของบริษัทเท่านั้น ทั้งนี้บริษัทจะดำเนินการให้เจ้าของข้อมูล รับรู้ ให้ความยินยอมโดยชัดแจ้งเป็นหนังสือหรือผ่านระบบอิเล็กทรอนิกส์ เช่น ข้อความสั้น (Short Message Service) หรือตามแบบวิธีการที่เหมาะสมและยอมรับได้ เว้นแต่เป็นข้อยกเว้นตามที่กฎหมายบัญญัติไว้') }}</p>
                    <p class="policy-sub">{{ __('2.2 บริษัทอาจจัดเก็บและใช้ข้อมูลส่วนบุคคลซึ่งเกี่ยวกับความสนใจและบริการที่ใช้ ซึ่งอาจประกอบด้วย เชื้อชาติ ศาสนาหรือปรัชญา ข้อมูลสุขภาพ ข้อมูลชีวภาพ ทุพพลภาพ ความพิการ อัตลักษณ์ หรือข้อมูลอื่นใด ที่จะเป็นประโยชน์ในการให้บริการ ทั้งนี้ การดำเนินการดังกล่าวข้างต้น จะเป็นไปตามที่กฎหมายบัญญัติไว้') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('3. มาตรการรักษาความมั่นคงปลอดภัยและคุณภาพของข้อมูล') }}</h2>
                    <p class="policy-sub">{{ __('3.1 บริษัทตระหนักถึงความสำคัญของการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคล บริษัทจึงกำหนดให้มีมาตรการในการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคลอย่างเหมาะสมและสอดคล้องเป็นไปตามที่กฎหมายบัญญัติไว้') }}</p>
                    <p class="policy-sub">{{ __('3.2 ข้อมูลส่วนบุคคลที่บริษัทได้รับมา เช่น ชื่อ อายุ ที่อยู่ หมายเลขโทรศัพท์ หมายเลขบัตรประชาชน ข้อมูลทางการเงิน เป็นต้น ซึ่งสามารถบ่งบอกตัวบุคคลได้ และเป็นข้อมูลส่วนบุคคลที่มีความถูกต้องและเป็นปัจจุบัน บริษัทจะดำเนินมาตรการที่เหมาะสมเพื่อคุ้มครองสิทธิของเจ้าของข้อมูลส่วนบุคคล') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('4. วัตถุประสงค์ในการรวบรวม จัดเก็บ ใช้ ข้อมูลส่วนบุคคล') }}</h2>
                    <p>{{ __('บริษัทรวบรวม จัดเก็บ ใช้ ข้อมูลส่วนบุคคล เพื่อประโยชน์ในการให้บริการ เช่น บริการระบบรักษาความปลอดภัย บริการประกันภัย บริการเช่า/จองตู้เซฟนิรภัย บริการซ่อม ติดตั้ง และบำรุงรักษาโดยช่างของบริษัทฯ บริการช่องทางชำระเงิน ติดตามทวงถามการชำระเงิน หรือการจัดทำบริการทางดิจิทัลผ่านแอปพลิเคชัน AEG APP หรือการวิจัยตลาดและการจัดกิจกรรมส่งเสริมการขาย หรือเพื่อจัดทำฐานข้อมูลและใช้ข้อมูลเพื่อเสนอสิทธิประโยชน์ผ่านระบบสมาชิก EASE CLUB หรือเพื่อประโยชน์ในการวิเคราะห์และนำเสนอบริการหรือผลิตภัณฑ์ใดๆ') }}</p>
                    <p>{{ __('รวมถึงการตรวจสอบเพื่อใช้ในการดำเนินการที่เกี่ยวข้องในกรณีที่มีการกระทำผิดเงื่อนไขการใช้บริการ หรือผิดกฎหมายใดๆ และเพื่อวัตถุประสงค์อื่นใดภายใต้กฎหมาย และ/หรือเพื่อปฏิบัติตามกฎหมายหรือกฎระเบียบที่ใช้บังคับกับการให้บริการ') }}</p>
                    <p>{{ __('รวมทั้ง ส่ง โอน และ/หรือเปิดเผยข้อมูลส่วนบุคคลให้แก่บริษัท กลุ่มธุรกิจของ บริษัท แองโกล อีสต์ กรุป จำกัด ได้แก่ บริษัท รักษาความปลอดภัย แองโกล อีสต์ (ประเทศไทย) จำกัด, บริษัท แองโกล อีสต์ ชัวร์ตี้ โบรกเกอร์ จำกัด, บริษัท นพศรซิเคียวริเทค จำกัด และบริษัท จิวเวล เอ็กซ์เพรส จำกัด รวมทั้งพันธมิตรทางธุรกิจ ผู้ให้บริการภายนอก ผู้ประมวลผลข้อมูล ผู้สนใจจะเข้ารับโอนสิทธิ ผู้รับโอนสิทธิ หน่วยงาน/องค์กร/นิติบุคคลใดๆ ที่มีสัญญาอยู่กับผู้ให้บริการหรือมีความสัมพันธ์ด้วย') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('5. สิทธิของเจ้าของข้อมูลส่วนบุคคล') }}</h2>
                    <p class="policy-sub">{{ __('5.1 ขอเข้าถึง ขอรับสำเนาข้อมูลส่วนบุคคล ตามหลักเกณฑ์และวิธีการที่บริษัทกำหนด ณ สำนักงานบริการแองโกล อีสต์ กรุป หรือขอให้เปิดเผยการได้มาซึ่งข้อมูลส่วนบุคคล') }}</p>
                    <p class="policy-sub">{{ __('5.2 ขอแก้ไขหรือเปลี่ยนแปลงข้อมูลส่วนบุคคลที่ไม่ถูกต้องหรือไม่สมบูรณ์ และทำให้ข้อมูลเป็นปัจจุบันได้') }}</p>
                    <p class="policy-sub">{{ __('5.3 ขอลบหรือทำลายข้อมูลส่วนบุคคล เว้นแต่เป็นกรณีที่ต้องปฏิบัติตามกฎหมายที่เกี่ยวข้อง') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('6. การเปิดเผยเกี่ยวกับการดำเนินการ แนวปฏิบัติ และนโยบายที่เกี่ยวกับข้อมูลส่วนบุคคล') }}</h2>
                    <p>{{ __('บริษัทฯ มีนโยบายปฏิบัติตามกฎหมาย รวมถึงพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 และประกาศ ระเบียบ หรือแนวปฏิบัติที่ออกโดยสำนักงานคณะกรรมการคุ้มครองข้อมูลส่วนบุคคล (สคส.) ตลอดจนกฎหมายอื่นที่เกี่ยวข้องกับการประกอบธุรกิจของบริษัทฯ และกลุ่มบริษัทในเครือ บริษัท แองโกล อีสต์ กรุป จำกัด') }}</p>
                    <p>{{ __('สำหรับบริการประกันภัยที่นำเสนอผ่านแอปพลิเคชัน AEG APP บริการดังกล่าวดำเนินการโดยบริษัทในเครือของ บริษัท แองโกล อีสต์ กรุป จำกัด ซึ่งได้รับใบอนุญาตประกอบธุรกิจประกันภัยจากสำนักงานคณะกรรมการกำกับและส่งเสริมการประกอบธุรกิจประกันภัย (คปภ.) อย่างถูกต้องตามกฎหมาย โดยการเก็บรวบรวมใช้ และเปิดเผยข้อมูลส่วนบุคคลสำหรับบริการประกันภัยจะเป็นไปตามกฎหมายว่าด้วยการประกันวินาศภัย/ประกันชีวิต และประกาศ คปภ. ที่เกี่ยวข้อง ควบคู่ไปกับกฎหมาย PDPA') }}</p>
                    <p>{{ __('บริษัทฯ จะออกมาตรการคุ้มครองข้อมูลผู้ใช้บริการ และเผยแพร่นโยบายฉบับนี้บนหน้าเว็บไซต์และแอปพลิเคชัน AEG APP เพื่อให้ท่านสามารถเข้าถึงและตรวจสอบแนวปฏิบัติด้านข้อมูลส่วนบุคคลของบริษัทฯ ได้ตลอดเวลา') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('7. เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล') }}</h2>
                    <p>{{ __('บริษัทได้ดำเนินการแต่งตั้งเจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล (Data Protection Officer : DPO) เพื่อตรวจสอบการดำเนินการของบริษัทที่เกี่ยวกับการเก็บรวบรวม ใช้และเปิดเผยข้อมูลส่วนบุคคลให้สอดคล้องกับพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 และกฎหมายที่เกี่ยวข้อง') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('8. ช่องทางการติดต่อบริษัท') }}</h2>
                    <p>{{ __('ฝ่ายบริการลูกค้าสัมพันธ์ (Customer Service) กลุ่มบริษัท แองโกล อีสต์ กรุป จำกัด Email: app@aeginc.co หรือโทร 02-238-4561') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('9. การบังคับใช้') }}</h2>
                    <p>{{ __('เพื่อให้การปฏิบัติเป็นไปตามนโยบายฉบับนี้ บริษัทได้แต่งตั้งคณะกรรมการข้อมูลส่วนบุคคลและความมั่นคงปลอดภัยไซเบอร์ ("คณะกรรมการฯ") โดยให้คณะกรรมการฯ ชุดดังกล่าว มีอำนาจหน้าที่ในการบริหารจัดการการคุ้มครองข้อมูลส่วนบุคคล โดยออกแนวทางปฏิบัติ หรือ แนวทางดำเนินการ รวมถึงการแก้ไข ปรับปรุง แนวทางปฏิบัติ หรือ แนวทางดำเนินการ ในการจัดการบริหารข้อมูลส่วนบุคคลของบริษัท โดยได้รับการอนุมัติจากประธานเจ้าหน้าที่บริหาร เพื่อกำหนดแนวทางการดำเนินการด้านข้อมูลส่วนบุคคลระหว่างบริษัทกับผู้มีส่วนได้เสียทั้งหมดต่อไป') }}</p>
                </div>

                <div class="policy-footer-action">
                    <a href="index" class="btn-accept">{{ __('ยอมรับ') }}</a>
                </div>
            </article>
        </div>
    </main>


    <!-- Footer Section -->
@endsection
