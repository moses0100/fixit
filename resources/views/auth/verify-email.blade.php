@extends('layouts.fixit')
@section('title', 'ยืนยันอีเมล')
@section('content')
<div class="auth-shell"><div class="panel auth-card"><h1 class="h4 fw-bold">ยืนยันอีเมลของคุณ</h1><p class="page-lead">เปิดลิงก์ที่ส่งไปยังอีเมลเพื่อยืนยันบัญชี หากยังไม่ได้รับ สามารถส่งอีกครั้งได้</p><form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary w-100">ส่งอีเมลยืนยันอีกครั้ง</button></form></div></div>
@endsection
