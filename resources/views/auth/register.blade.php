<!-- แสดง Error Message หากกรอกข้อมูลไม่ถูกต้อง -->
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- ฟอร์มลงทะเบียน -->
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div>
        <label for="name">ชื่อ-นามสกุล</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
    </div>

    <div>
        <label for="email">อีเมล</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label for="phone">เบอร์โทรศัพท์</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required>
    </div>

    <div>
        <label for="password">รหัสผ่าน (ขั้นต่ำ 8 ตัวอักษร)</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div>
        <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit">ลงทะเบียน</button>
</form>

<a href="{{ route('login') }}">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
