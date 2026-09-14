@extends('layouts.fixit')
@section('title', 'ดูแลอุปกรณ์ ให้พร้อมสำหรับทุกวัน')
@section('content')
<div class="landing">
<div class="row g-5 align-items-center"><div class="col-lg-6"><div class="eyebrow">COMPUTER REPAIR & SUPPORT</div><h1>เรื่องซ่อมคอม<br>ให้เป็นเรื่องง่าย<span style="color:#79a07c">.</span></h1><p class="page-lead my-4 fs-5">แจ้งปัญหาอุปกรณ์ แนบรูปอาการเสีย<br>และติดตามทุกความคืบหน้าได้ในที่เดียว</p><div class="d-flex gap-3 flex-wrap"><a class="btn btn-primary" href="{{ auth()->check() ? route('repairs.create') : route('register') }}">เริ่มแจ้งซ่อม →</a><a class="btn btn-outline-secondary" href="{{ auth()->check() ? route('dashboard') : route('login') }}">ติดตามรายการของฉัน</a></div><p class="small text-muted mt-4">สำหรับคอมพิวเตอร์ โน้ตบุ๊ก และอุปกรณ์ที่เกี่ยวข้อง</p></div><div class="col-lg-6"><div class="hero-art">@include('partials.computer')</div></div></div>
<div class="row g-4 mt-5">@foreach(['01' => ['แจ้งปัญหา','บอกอาการที่พบและแนบรูปภาพ เพื่อให้ผู้ดูแลตรวจสอบได้ตรงจุด'], '02' => ['ติดตามความคืบหน้า','ดูสถานะตั้งแต่รอตรวจสอบจนถึงซ่อมเสร็จ พร้อมหมายเหตุจากผู้ดูแล'], '03' => ['กลับมาพร้อมใช้งาน','ตรวจผลการซ่อมและวันที่ปิดงานได้จากเลขอ้างอิงของคุณ']] as $n => $item)<div class="col-md-4"><div class="panel panel-body h-100"><div class="eyebrow">{{ $n }} / HOW IT WORKS</div><h2 class="h5 fw-bold">{{ $item[0] }}</h2><p class="page-lead mb-0 mt-3">{{ $item[1] }}</p></div></div>@endforeach</div>
</div>
@endsection
