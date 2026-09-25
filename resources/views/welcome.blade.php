@extends('layouts.fixit')
@section('title', 'ดูแลอุปกรณ์ ให้พร้อมสำหรับทุกวัน')
@section('content')
<div class="landing">
<div class="row g-5 align-items-center"><div class="col-lg-6"><div class="eyebrow">COMPUTER REPAIR & SUPPORT</div><h1>เรื่องซ่อมคอม<br>ให้เป็นเรื่องง่าย<span style="color:#79a07c">.</span></h1><p class="page-lead my-4 fs-5">แจ้งปัญหาอุปกรณ์ แนบรูปอาการเสีย<br>และติดตามทุกความคืบหน้าได้ในที่เดียว</p><div class="d-flex gap-3 flex-wrap"><a class="btn btn-primary" href="{{ auth()->check() ? route('repairs.create') : route('register') }}">เริ่มแจ้งซ่อม →</a><a class="btn btn-outline-secondary" href="{{ auth()->check() ? route('dashboard') : route('login') }}">ติดตามรายการของฉัน</a></div><p class="small text-muted mt-4">สำหรับคอมพิวเตอร์ โน้ตบุ๊ก และอุปกรณ์ที่เกี่ยวข้อง</p></div><div class="col-lg-6"><div class="hero-art">@include('partials.computer')</div></div></div>
<div class="row g-4 mt-5">@foreach(['01' => ['แจ้งปัญหา','บอกอาการที่พบและแนบรูปภาพ เพื่อให้ผู้ดูแลตรวจสอบได้ตรงจุด'], '02' => ['ติดตามความคืบหน้า','ดูสถานะตั้งแต่รอตรวจสอบจนถึงซ่อมเสร็จ พร้อมหมายเหตุจากผู้ดูแล'], '03' => ['กลับมาพร้อมใช้งาน','ตรวจผลการซ่อมและวันที่ปิดงานได้จากเลขอ้างอิงของคุณ']] as $n => $item)<div class="col-md-4"><div class="panel panel-body h-100"><div class="eyebrow">{{ $n }} / HOW IT WORKS</div><h2 class="h5 fw-bold">{{ $item[0] }}</h2><p class="page-lead mb-0 mt-3">{{ $item[1] }}</p></div></div>@endforeach</div>
<div class="panel mt-5"><div class="panel-body"><div class="row g-4 text-center">
<div class="col-4"><div class="metric-value">{{ number_format($total ?? 0) }}</div><div class="metric-label">ใบแจ้งทั้งหมด</div></div>
<div class="col-4"><div class="metric-value">{{ number_format($doing ?? 0) }}</div><div class="metric-label">กำลังดำเนินการ</div></div>
<div class="col-4"><div class="metric-value">{{ number_format($done ?? 0) }}</div><div class="metric-label">ซ่อมเสร็จแล้ว</div></div>
</div></div></div>
<div class="mt-5" style="max-width:800px;margin:auto"><div class="eyebrow text-center">FAQ / คำถามที่พบบ่อย</div><h2 class="h4 fw-bold text-center mb-4">สงสัยตรงไหน ดูตรงนี้ก่อน</h2>
<div class="accordion" id="faq">
<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">แจ้งซ่อมต้องสมัครสมาชิกไหม?</button></h2><div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">ต้องสมัครก่อน ระบบจะผูกใบแจ้งกับบัญชีของคุณเพื่อให้ติดตามสถานะและดูประวัติเครื่องเดิมได้</div></div></div>
<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">ติดตามสถานะได้ที่ไหน?</button></h2><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">เมนูรายการแจ้งซ่อมของฉัน ทุกใบมีเลข REP-ปี-ลำดับ พร้อมไทม์ไลน์ว่าใครทำอะไรเมื่อไร</div></div></div>
<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">แนบรูปได้ไหม ไฟล์แบบไหน?</button></h2><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">ได้ รองรับ JPG PNG WebP ไม่เกิน 2 MB รูปเห็นเฉพาะเจ้าของงานกับผู้ดูแลเท่านั้น</div></div></div>
<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">ยกเลิกใบแจ้งได้ไหม?</button></h2><div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">ได้เฉพาะใบที่ยังรอตรวจสอบ ข้อมูลเดิมยังเก็บไว้ไม่หาย</div></div></div>
</div></div>
<div class="welcome-card mt-5 text-center"><h2>พร้อมส่งซ่อมแล้วหรือยัง?</h2><p class="page-lead">สมัครสมาชิกฟรี ใช้เวลาไม่ถึงนาที แล้วแจ้งซ่อมได้ทันที</p><div class="d-flex gap-3 justify-content-center flex-wrap"><a class="btn btn-primary" href="{{ route('register') }}">สมัครสมาชิก</a><a class="btn btn-outline-secondary" href="{{ route('login') }}">เข้าสู่ระบบ</a></div></div>
</div>
@endsection
