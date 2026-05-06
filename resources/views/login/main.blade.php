@extends('../layout/' . $layout)

@section('head')
    <title>Login - AEG</title>
@endsection

@section('body')
<body class="login">
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="Midone - HTML Admin Template" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3">AEG Admin</span>
                </a>
                <div class="my-auto">
                    <img alt="Midone - HTML Admin Template" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">ระบบจัดการหลังบ้าน <br> AEG Dashboard</div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">เข้าสู่ระบบเพื่อจัดการข้อมูลลูกค้าและการแจ้งซ่อม</div>
                </div>
            </div>
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0" style="background-color: white;">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Sign In</h2>
                    <div class="intro-x mt-8">
                        <form id="login-form">
                            <input id="email" type="text" class="intro-x login__input form-control py-3 px-4 block" placeholder="Email หรือ Username" value="admin@gmail.com">
                            <div id="error-email" class="login__input-error text-danger mt-2"></div>

                            <input id="password" type="password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password" value="password">
                            <div id="error-password" class="login__input-error text-danger mt-2"></div>
                        </form>
                    </div>
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                        <button id="btn-login" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">Login</button>
                    </div>
                </div>
            </div>
            </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // เซ็ต CSRF Token ให้ Axios ทำงานกับ Laravel ได้
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        (function () {
            async function login() {
                // Reset state
                $('#login-form').find('.login__input').removeClass('border-danger');
                $('#login-form').find('.login__input-error').html('');

                let email = $('#email').val();
                let password = $('#password').val();

                // Loading state (เปลี่ยนข้อความปุ่ม และปิดไม่ให้กดซ้ำ)
                $('#btn-login').html('Logging in...').prop('disabled', true);

                axios.post(`{{ route('login.check') }}`, {
                    email: email,
                    password: password
                }).then(res => {
                    // ล็อกอินสำเร็จ พากลับไปหน้า Dashboard
                    location.href = '{{ route("home") }}';
                }).catch(err => {
                    // คืนค่าปุ่มกลับมา
                    $('#btn-login').html('Login').prop('disabled', false);

                    if (err.response && err.response.data.errors) {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger');
                            $(`#error-${key}`).html(val);
                        }
                    } else if (err.response && err.response.data.message) {
                        $(`#password`).addClass('border-danger');
                        $(`#error-password`).html(err.response.data.message);
                    } else {
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    }
                });
            }

            // กด Enter เพื่อล็อกอินได้
            $('#login-form').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    login();
                }
            });

            $('#btn-login').on('click', function(e) {
                e.preventDefault();
                login();
            });
        })();
    </script>
</body>
@endsection
