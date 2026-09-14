@extends('layouts.fixit')
@section('title', 'ไม่มีสิทธิ์เข้าถึง')
@section('content')
<div class="auth-shell"><div class="panel panel-body text-center"><div class="eyebrow">403 / ACCESS DENIED</div><h1 class="h3">คุณไม่มีสิทธิ์เข้าถึงส่วนนี้</h1><p class="page-lead">เปิดได้เฉพาะรายการของคุณ หรือดำเนินการในสถานะที่ระบบอนุญาต</p><a class="btn btn-primary" href="{{ route('dashboard') }}">กลับหน้าภาพรวม</a></div></div>
@endsection
