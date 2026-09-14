@extends('layouts.fixit')
@section('title', 'ไม่พบข้อมูล')
@section('content')
<div class="auth-shell"><div class="panel panel-body text-center"><div class="eyebrow">404 / NOT FOUND</div><h1 class="h3">ไม่พบหน้าหรือรายการนี้</h1><p class="page-lead">กรุณาตรวจสอบลิงก์ หรือกลับไปเลือกจากหน้ารายการ</p><a class="btn btn-primary" href="{{ route('home') }}">กลับหน้าแรก</a></div></div>
@endsection
