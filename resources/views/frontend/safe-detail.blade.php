<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แพ็กเกจบริการตู้เซฟนิรภัย - AEG EASE CLUB</title>
    <!-- Google Fonts: Poppins (Main) & Kanit (Thai support) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
</head>

<body>

    <!-- Header Section -->
    @include('frontend.header')

    <!-- Main Content Section -->
    <main class="page-wrapper">
        <div class="container">
            <div class="safe-detail-card">
                <!-- Header Banner Image (Matching Safe.jpg) -->
                <img src="assets/image/safe-banner.webp" alt="แพ็กเกจบริการตู้เซฟนิรภัย" class="banner-image"
                    onerror="this.src='https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=1200&q=80'">

                <div class="card-body-content">
                    <div class="section-header">
                        <h1>แพ็กเกจบริการตู้เซฟนิรภัย</h1>
                    </div>

                    <!-- Content Group 1: กันไฟ -->
                    <div class="content-group">
                        <h2>ตู้เซฟนิรภัยกันไฟ</h2>
                        <ul class="detail-list">
                            <li>ป้องกันเอกสารและทรัพย์สินจากความร้อนและเปลวไฟ</li>
                            <li>โครงสร้างวัสดุทนไฟ ป้องกันความเสียหายจากอัคคีภัย</li>
                            <li>เสริมความปลอดภัยด้วยระบบล็อกหลากหลายรูปแบบ</li>
                        </ul>
                    </div>

                    <!-- Content Group 2: กันโจรกรรม -->
                    <div class="content-group">
                        <h2>ตู้เซฟนิรภัยกันการเจาะโจรกรรม</h2>
                        <ul class="detail-list">
                            <li>โครงสร้างเหล็กหนา แข็งแรง ทนต่อการงัดแงะและการเจาะทำลาย</li>
                            <li>ระบบล็อกหลายชั้น เพิ่มความปลอดภัยต่อเหตุการณ์ไม่คาดฝัน</li>
                            <li>สามารถติดตั้งยึดกับพื้นหรือผนังเพื่อป้องกันการเคลื่อนย้าย</li>
                        </ul>
                    </div>

                    <!-- Action Button Link to Contact -->
                    <div class="btn-consult-wrapper">
                        <a href="{{ route('insurance-contact') }}" class="btn-gradient-pill">ปรึกษาผู้เชี่ยวชาญ</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Chat Icon -->
    <div class="floating-chat">
        <div class="chat-circle">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
                    <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                        <div class="bg-white p-2 rounded d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-qrcode fa-3x text-dark"></i>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                                    height="35" alt="App Store"></a>
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                                    height="35" alt="Play Store"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.9rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="#" class="footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.9rem;">คำถามที่พบบ่อย</h5>
                            <a href="#" class="footer-link">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                    <div class="social-icons-bar">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
