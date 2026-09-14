@extends('layouts.fixit')
@section('title', 'ลืมรหัสผ่าน')
@section('content')
<div class="auth-shell"><div class="panel auth-card"><h1 class="h4 fw-bold">ลืมรหัสผ่าน?</h1><p class="page-lead">กรอกอีเมลที่ใช้สมัคร เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่</p><form method="POST" action="{{ route('password.email') }}">@csrf<label class="form-label" for="email">อีเมล</label><input class="form-control mb-4" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus><button class="btn btn-primary w-100" type="submit">ส่งลิงก์ตั้งรหัสผ่านใหม่</button><a class="d-block text-center small mt-4" href="{{ route('login') }}">กลับเข้าสู่ระบบ</a></form></div></div>
@endsection
