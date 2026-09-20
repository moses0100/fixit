@extends('layouts.fixit')
@section('title', $admin ? 'จัดการงานซ่อม' : 'รายการแจ้งซ่อมของฉัน')
@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap"><div><div class="eyebrow">REPAIR REQUESTS</div><h1 class="page-title">{{ $admin ? 'จัดการงานซ่อม' : 'รายการแจ้งซ่อมของฉัน' }}</h1><p class="page-lead mb-0">{{ $admin ? 'ตรวจสอบและดูแลทุกคำขอแจ้งซ่อมในระบบ' : 'ค้นหาและติดตามความคืบหน้าของอุปกรณ์ของคุณ' }}</p></div><a href="{{ route('repairs.create') }}" class="btn btn-primary">＋ แจ้งซ่อมใหม่</a></div>
<div class="panel">
<form method="GET" class="filters row g-3 m-0" data-live-search action="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">
    <div class="col-lg-4"><label class="form-label" for="q">ค้นหารายการ</label><input class="form-control" id="q" name="q" value="{{ request('q') }}" maxlength="150" placeholder="เลขแจ้งซ่อม / อุปกรณ์ / Serial / ชื่อผู้แจ้ง" autocomplete="off"></div>
    <div class="col-md-4 col-lg-2"><label class="form-label" for="status">สถานะ</label><select name="status" id="status" class="form-select"><option value="">ทั้งหมด</option>@foreach(\App\Models\RepairRequest::STATUSES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 col-lg-2"><label class="form-label" for="urgency">ความเร่งด่วน</label><select name="urgency" id="urgency" class="form-select"><option value="">ทั้งหมด</option>@foreach(\App\Models\RepairRequest::URGENCIES as $value => $label)<option value="{{ $value }}" @selected(request('urgency') === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-4 col-lg-2"><label class="form-label" for="sort">เรียงลำดับ</label><select name="sort" id="sort" class="form-select"><option value="newest">ใหม่ที่สุด</option><option value="oldest" @selected(request('sort') === 'oldest')>เก่าที่สุด</option></select></div>
    <div class="col-lg-2 d-flex gap-2 align-items-end"><button class="btn btn-primary" type="submit">ค้นหา</button><a class="btn btn-light" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">ล้าง</a></div>
</form>
<div class="panel-head"><h2>รายการทั้งหมด <span class="text-muted fw-normal small" data-live-total>/ {{ $repairs->total() }} รายการ</span></h2><span class="small text-muted" data-live-hint></span></div>
<div data-live-result>@include('repairs.table')
@if($repairs->hasPages())<div class="p-4" data-live-pagination>{{ $repairs->links() }}</div>@endif</div>
</div>
@endsection
