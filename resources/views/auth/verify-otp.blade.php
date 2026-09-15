@extends('layouts.fixit')
@section('title', 'ยืนยัน OTP')
@section('content')
<div class="auth-shell"><div class="panel auth-card"><h1 class="h4 fw-bold">ยืนยันรหัส OTP</h1><p class="page-lead">กรอกรหัส 6 หลักที่ส่งไปยัง {{ $email }} รหัสมีอายุ 10 นาที</p><form method="POST" action="{{ route('password.otp.verify') }}">@csrf<input type="hidden" name="email" value="{{ $email }}"><label class="form-label" for="otp">รหัส OTP</label><input class="form-control mb-4" type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" autofocus><button class="btn btn-primary w-100" type="submit">ยืนยันรหัส OTP</button><a class="d-block text-center small mt-4" href="{{ route('password.request') }}">ขอรหัสใหม่</a></form></div></div>
@endsection
