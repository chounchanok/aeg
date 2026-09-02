<footer>
    <?php $setting = DB::table('general_setting')->where('id', 1)->first(); ?>
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center text-md-start">
                    <h6 class="fw-bold mb-3">{{ __('ดาวน์โหลดแอปพลิเคชัน') }}</h6>
                    <div class="d-flex justify-content-center justify-content-md-start gap-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode(route('app.download')) }}"
                            class="bg-white p-1 rounded" alt="{{ __('QR Code สำหรับดาวน์โหลดแอป') }}" width="80">
                        <div class="d-flex flex-column gap-2">
                            <a href="https://apps.apple.com/us/app/anglo-east-surety/id6786582784" target="_blank"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                                    height="28"></a>
                            <a href="https://play.google.com/store/apps/details?id=com.aeginc.angloeast" target="_blank"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                                    height="28"></a>
                        </div>
                    </div>
                </div>
        
                <div class="col-md-4 text-center">
                    <div class="row">
                        <div class="col-6 footer-column footer-divider">
                            <a href="{{ route('packages.mine') }}" class="d-block text-white-50 text-decoration-none mb-2">{{ __('แพ็กเกจที่ใช้งาน') }}</a>
                            <a href="{{ route('terms-conditions') }}" class="d-block text-white-50 text-decoration-none mb-2">{{ __('ข้อกำหนดและเงื่อนไข') }}</a>
                        </div>
                        <div class="col-6 footer-column">
                            <a href="{{ route('faq') }}" class="d-block text-white-50 text-decoration-none mb-2">{{ __('ช่วยเหลือ') }}</a>
                            <a href="{{ route('privacy-policy') }}" class="d-block text-white-50 text-decoration-none mb-2">{{ __('นโยบายความเป็นส่วนตัว') }}</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center text-md-end">
                    <div class="logo-container" style="position: relative; display: inline-block; max-width: 150px; width: 100%;">
                    <img src="{{ asset('assets/image/logo.webp') }}" alt="AEG EASE CLUB" style="width: 100%; height: auto; display: block;">

                    <a href="{{ route('home') }}"
                    style="position: absolute; top: 0; left: 0; width: 40%; height: 100%; z-index: 10;"
                    title="{{ __('หน้าหลัก') }}"></a>

                    <a href="{{ route('rewards') }}"
                    style="position: absolute; top: 0; right: 0; width: 60%; height: 100%; z-index: 10;"
                    title="{{ __('สิทธิพิเศษ EASE CLUB') }}"></a>
                </div>
                </div>
            </div>

            <div class="row mt-5 align-items-center">
                <div class="col-md-4 d-none d-md-block"></div>
                <div class="col-md-4 text-center">
                    <div class="social-icons">
                        <a href="{{ $setting->facebook_url }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="{{ $setting->instagram_url }}" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="{{ $setting->line_url }}" target="_blank"><i class="fab fa-line"></i></a>
                        <a href="{{ $setting->tiktok_url }}" target="_blank"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                    <p class="copyright-text mb-0">© 2026 AEG EASE CLUB. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
