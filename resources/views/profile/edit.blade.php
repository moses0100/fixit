@extends('layouts.fixit')
@section('title', 'ข้อมูลบัญชี')
@section('content')
<div class="eyebrow">ACCOUNT SETTINGS</div><h1 class="page-title">ข้อมูลบัญชีของคุณ</h1><p class="page-lead mb-4">แก้ไขข้อมูลติดต่อและดูแลความปลอดภัยของบัญชี</p>
<div class="row g-4"><div class="col-lg-7"><div class="panel"><div class="panel-head"><h2>ข้อมูลส่วนตัว</h2></div><form class="panel-body" method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
<label class="form-label" for="name">ชื่อ</label><input id="name" name="name" class="form-control mb-3" required maxlength="255" value="{{ old('name', $user->name) }}" autocomplete="name">
<label class="form-label" for="email">อีเมล</label><input id="email" name="email" type="email" class="form-control mb-3" required maxlength="255" value="{{ old('email', $user->email) }}" autocomplete="username">
<p class="text-muted small">บทบาท: {{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งานทั่วไป' }}</p>
<button class="btn btn-primary" type="submit">บันทึกข้อมูล</button>
</form></div>
<div class="panel mt-4"><div class="panel-head"><h2>เปลี่ยนรหัสผ่าน</h2></div><div class="panel-body">@if($user->google_id)<p class="page-lead mb-0">บัญชีนี้เข้าสู่ระบบด้วย Google ไม่ต้องใช้รหัสผ่าน ถ้าอยากตั้งรหัสผ่านให้กดลืมรหัสผ่านที่หน้าเข้าสู่ระบบ</p>@else<form method="POST" action="{{ route('password.update') }}">@csrf @method('PUT')
@if($errors->updatePassword->any())<div class="alert alert-danger">@foreach($errors->updatePassword->all() as $message)<div>{{ $message }}</div>@endforeach</div>@endif
<label class="form-label" for="current_password">รหัสผ่านปัจจุบัน</label><input class="form-control mb-3" id="current_password" name="current_password" type="password" required autocomplete="current-password">
<label class="form-label" for="password">รหัสผ่านใหม่</label><input class="form-control mb-3" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
<label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่</label><input class="form-control mb-4" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"><button class="btn btn-primary" type="submit">เปลี่ยนรหัสผ่าน</button>
</form>@endif</div></div></div><div class="col-lg-5"><div class="panel"><div class="panel-head"><h2>ลบบัญชี</h2></div><div class="panel-body">
@if($user->repairRequests()->exists())<p class="page-lead mb-0">บัญชีนี้มีประวัติแจ้งซ่อมอยู่ จึงไม่สามารถลบบัญชีได้ เพื่อเก็บข้อมูลอ้างอิงของรายการซ่อม</p>@else
<p class="page-lead">เมื่อลบบัญชีแล้วจะไม่สามารถกู้คืนได้ กรุณาตรวจสอบก่อนดำเนินการ</p>
<form method="POST" action="{{ route('profile.destroy') }}" data-confirm="ยืนยันลบบัญชีถาวร? ไม่สามารถกู้คืนได้">@csrf @method('DELETE')
@if($errors->userDeletion->any())<div class="alert alert-danger">@foreach($errors->userDeletion->all() as $message)<div>{{ $message }}</div>@endforeach</div>@endif
<label class="form-label" for="delete_password">ยืนยันรหัสผ่าน</label><input class="form-control mb-3" type="password" id="delete_password" name="password" autocomplete="current-password" required><button class="btn btn-outline-danger" type="submit">ลบบัญชีถาวร</button>
</form>@endif</div></div></div></div>
@endsection
