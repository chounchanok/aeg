@extends('../layout/login')

@section('head')
    <title>เข้าสู่ระบบหลังบ้าน - AEG Admin</title>
    <style>
        /* CSS เฉพาะหน้านี้ (prefix al- — ห้ามใช้ ad- เพราะตัวบล็อกโฆษณาจะซ่อน) */
        .al-pass { position: relative; }
        .al-pass .al-eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: 0; color: #94a3b8; cursor: pointer; padding: 4px; }
        .al-alert { padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-top: 16px; }
        .al-alert.err { background: #fee2e2; color: #991b1b; }
        .al-alert.ok { background: #dcfce7; color: #166534; }
        .al-foot { font-size: 13px; color: #64748b; margin-top: 24px; line-height: 1.7; }
        .al-foot a { color: #1e3a8a; text-decoration: underline; }
    </style>
@endsection

@section('content')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            {{-- ฝั่งซ้าย (จอใหญ่) --}}
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="{{ route('admin.login') }}" class="-intro-x flex items-center pt-5">
                    <img alt="AEG" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3">AEG Admin</span>
                </a>
                <div class="my-auto">
                    <img alt="" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">ระบบจัดการหลังบ้าน<br>AEG Admin</div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">สำหรับพนักงานและช่างซ่อม</div>
                </div>
            </div>

            {{-- ฟอร์มเข้าสู่ระบบ --}}
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">เข้าสู่ระบบหลังบ้าน</h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">ระบบจัดการหลังบ้าน AEG สำหรับพนักงานและช่างซ่อม</div>

                    @if (session('success'))
                        <div class="al-alert ok">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="al-alert err">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.check') }}" class="intro-x mt-8" id="adminLoginForm">
                        @csrf
                        <label for="phone" class="form-label">เบอร์มือถือ</label>
                        <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="username" autofocus
                               value="{{ old('phone') }}" placeholder="เช่น 0812345678"
                               class="login__input form-control py-3 px-4 block {{ $errors->has('phone') ? 'border-danger' : '' }}">

                        <label for="password" class="form-label mt-4">รหัสผ่าน</label>
                        <div class="al-pass">
                            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="รหัสผ่าน"
                                   class="login__input form-control py-3 px-4 block {{ $errors->has('password') ? 'border-danger' : '' }}" style="padding-right: 44px;">
                            <button type="button" class="al-eye" id="togglePass" aria-label="แสดงรหัสผ่าน">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                            <label class="flex items-center mr-auto cursor-pointer">
                                <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input border mr-2" {{ old('remember') ? 'checked' : '' }}>
                                จดจำการเข้าสู่ระบบ
                            </label>
                        </div>

                        <div class="mt-5 xl:mt-8 text-center xl:text-left">
                            <button type="submit" id="btnLogin" class="btn btn-primary py-3 px-4 w-full xl:w-40 align-top">เข้าสู่ระบบ</button>
                        </div>
                    </form>

                    <div class="intro-x al-foot text-center xl:text-left">
                        ลืมรหัสผ่าน? ติดต่อผู้ดูแลระบบ (IT) เพื่อตั้งรหัสผ่านใหม่<br>
                        ลูกค้าทั่วไป <a href="{{ route('login') }}">เข้าสู่ระบบที่หน้าเว็บไซต์</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    (function () {
        var btn = document.getElementById('togglePass');
        var input = document.getElementById('password');
        if (btn && input) {
            btn.addEventListener('click', function () {
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        }
        var form = document.getElementById('adminLoginForm');
        if (form) {
            form.addEventListener('submit', function () {
                var b = document.getElementById('btnLogin');
                b.disabled = true;
                b.textContent = 'กำลังเข้าสู่ระบบ...';
            });
        }
    })();
</script>
@endsection
