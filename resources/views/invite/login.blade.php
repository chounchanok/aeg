<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ยืนยันการเข้าร่วม - AEG</title>
    <link rel="stylesheet" href="{{ asset('dist/css/app.css') }}" />
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
        <div class="text-center mb-8">
            <img src="{{ asset('dist/images/logo.svg') }}" alt="Logo" class="w-16 h-16 mx-auto mb-4">
            <h2 class="text-2xl font-bold text-slate-800">ยืนยันการเข้าร่วม</h2>
            <p class="text-slate-500 mt-2">กรุณาเข้าสู่ระบบเพื่อตอบรับคำเชิญ</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4 text-red-600 bg-red-50 p-3 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('invite.process') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="role" value="{{ $role }}">
            <input type="hidden" name="id" value="{{ $id }}">
            <input type="hidden" name="expire" value="{{ $expire }}">

            <div class="mb-4">
                <label class="block text-slate-700 mb-2">อีเมล</label>
                <input type="email" name="email" class="form-control w-full px-4 py-2 border rounded-lg" required autofocus>
            </div>

            <div class="mb-6">
                <label class="block text-slate-700 mb-2">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control w-full px-4 py-2 border rounded-lg" required>
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                เข้าสู่ระบบและเข้าร่วม
            </button>
        </form>
    </div>

</body>
</html>
