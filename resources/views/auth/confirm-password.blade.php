@extends('layouts.fixit')
@section('title', 'ยืนยันรหัสผ่าน')
@section('content')
<div class="auth-shell"><div class="panel auth-card"><h1 class="h4 fw-bold">ยืนยันรหัสผ่าน</h1><p class="page-lead">กรอกรหัสผ่านเพื่อดำเนินการต่อ</p><form method="POST" action="{{ route('password.confirm') }}">@csrf<label class="form-label" for="password">รหัสผ่าน</label><input class="form-control mb-4" id="password" name="password" type="password" required autocomplete="current-password"><button class="btn btn-primary w-100">ยืนยัน</button></form></div></div>
@endsection
