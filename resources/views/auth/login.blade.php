@extends('layouts.fixit')
@section('title', 'เข้าสู่ระบบ')
@section('content')
<div class="auth-shell"><div class="row g-5 align-items-center"><div class="col-md-6 d-none d-md-block"><div class="eyebrow">WELCOME BACK</div><h1 class="page-title">กลับมาดูแลอุปกรณ์<br>ให้พร้อมใช้งานอีกครั้ง</h1><p class="page-lead mt-3">เข้าสู่ระบบเพื่อแจ้งซ่อม<br>หรือติดตามรายการที่คุณส่งไว้</p><div style="max-width:330px">@include('partials.computer')</div></div><div class="col-md-6"><div class="panel auth-card"><h2 class="h4 fw-bold">เข้าสู่ระบบ</h2><p class="text-muted mb-4">ยินดีต้อนรับกลับสู่ FixIT</p>
<form method="POST" action="{{ route('login') }}">@csrf
<label for="email" class="form-label">อีเมล</label><input id="email" name="email" type="email" class="form-control mb-3" value="{{ old('email') }}" required autofocus autocomplete="username">
<label for="password" class="form-label">รหัสผ่าน</label><input id="password" name="password" type="password" class="form-control mb-3" required autocomplete="current-password">
<div class="d-flex justify-content-between small mb-4"><label><input type="checkbox" name="remember" class="form-check-input me-1"> จดจำฉัน</label><a href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a></div>
<button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ →</button><a class="btn btn-outline-secondary w-100 mt-2" href="{{ route('google.redirect') }}">เข้าสู่ระบบด้วย Google</a><p class="small text-center mt-4 mb-0">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></p>
</form></div></div></div></div>
@endsection
