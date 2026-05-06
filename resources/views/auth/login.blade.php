<!-- แสดง Error Message หาก Login ไม่ผ่าน -->
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- ฟอร์มเข้าสู่ระบบ -->
<form method="POST" action="{{ route('signin') }}">
    @csrf

    <div>
        <label for="email">อีเมล</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
    </div>

    <div>
        <label for="password">รหัสผ่าน</label>
        <input type="password" id="password" name="password" required>
    </div>

    <button type="submit">เข้าสู่ระบบ</button>
</form>

<a href="{{ route('register') }}">ยังไม่มีบัญชี? ลงทะเบียนที่นี่</a>
