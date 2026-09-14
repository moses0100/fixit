@extends('layouts.fixit')
@section('title', 'สมัครสมาชิก')
@section('content')
<div class="auth-shell"><div class="panel auth-card"><div class="eyebrow">START WITH FIXIT</div><h1 class="h4 fw-bold">สร้างบัญชีของคุณ</h1><p class="text-muted mb-4">เริ่มแจ้งซ่อมและติดตามอุปกรณ์ได้เลย</p><form method="POST" action="{{ route('register') }}">@csrf
<label class="form-label" for="name">ชื่อผู้ใช้งาน</label><input class="form-control mb-3" id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>
<label class="form-label" for="email">อีเมล</label><input class="form-control mb-3" id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="username">
<label class="form-label" for="password">รหัสผ่าน</label><input class="form-control" id="password" name="password" type="password" required minlength="8" autocomplete="new-password"><div class="form-text mb-3">อย่างน้อย 8 ตัวอักษร</div>
<label class="form-label" for="password_confirmation">ยืนยันรหัสผ่าน</label><input class="form-control mb-4" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
<button type="submit" class="btn btn-primary w-100">สมัครสมาชิก</button><p class="small text-center mt-4 mb-0">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a></p>
</form></div></div>
@endsection
