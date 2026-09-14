@extends('layouts.fixit')
@section('title', $repair->exists ? 'แก้ไขรายการแจ้งซ่อม' : 'แจ้งซ่อมใหม่')
@section('content')
<a class="small" href="{{ route('repairs.index') }}">← กลับไปรายการแจ้งซ่อม</a>
<div class="eyebrow mt-4">NEW REPAIR / DEVICE SUPPORT</div>
<h1 class="page-title">{{ $repair->exists ? 'แก้ไขรายการแจ้งซ่อม' : 'ให้อุปกรณ์ของคุณกลับมาพร้อมใช้งาน' }}</h1>
<p class="page-lead mb-4">กรอกรายละเอียดให้ครบ เพื่อให้ผู้ดูแลตรวจสอบปัญหาได้ตรงจุด</p>
<form action="{{ $repair->exists ? route('repairs.update', $repair) : route('repairs.store') }}" method="POST" enctype="multipart/form-data">
@csrf
@if($repair->exists) @method('PUT') @endif
<div class="row g-4"><div class="col-lg-8">
<div class="panel mb-4"><div class="panel-head"><h2><span class="section-number">01</span> ข้อมูลอุปกรณ์</h2></div><div class="panel-body row g-3">
<div class="col-md-6"><label class="form-label" for="device_type">ประเภทอุปกรณ์ *</label><select class="form-select" name="device_type" id="device_type" required>@foreach(\App\Models\RepairRequest::DEVICES as $type)<option @selected(old('device_type', $repair->device_type) === $type)>{{ $type }}</option>@endforeach</select></div>
@foreach(['brand' => 'ยี่ห้อ *', 'model' => 'รุ่น', 'serial_number' => 'Serial Number'] as $field => $label)
<div class="col-md-6"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" maxlength="100" value="{{ old($field, $repair->$field) }}" @required($field === 'brand')>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endforeach
</div></div>
<div class="panel"><div class="panel-head"><h2><span class="section-number">02</span> รายละเอียดปัญหา</h2></div><div class="panel-body">
<label class="form-label" for="title">หัวข้อปัญหา *</label><input class="form-control @error('title') is-invalid @enderror mb-3" id="title" name="title" maxlength="150" required value="{{ old('title', $repair->title) }}" placeholder="เช่น เปิดเครื่องไม่ติด หรือหน้าจอไม่แสดงผล">
<label class="form-label" for="problem_description">อาการที่พบ *</label><textarea class="form-control mb-3" id="problem_description" name="problem_description" rows="6" maxlength="5000" required placeholder="อธิบายอาการ เกิดขึ้นเมื่อไหร่ และสิ่งที่ได้ลองแก้ไขแล้ว">{{ old('problem_description', $repair->problem_description) }}</textarea>
<label class="form-label" for="image">รูปภาพประกอบ</label><input class="form-control" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input><div class="form-text">JPG, PNG หรือ WebP ขนาดไม่เกิน 2 MB · รูปใหม่จะแทนที่รูปเดิม</div>
<img data-image-preview class="attachment mt-3" alt="ตัวอย่างรูปที่เลือก" hidden>
@if($repair->image)<img class="attachment mt-3" src="{{ route('repairs.image', $repair) }}" alt="รูปปัจจุบัน"><label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="remove_image" value="1"><span class="form-check-label">ลบรูปเดิม</span></label>@endif
</div></div>
</div><div class="col-lg-4"><div class="panel mb-4"><div class="panel-head"><h2><span class="section-number">03</span> การติดต่อ</h2></div><div class="panel-body">
<label class="form-label" for="urgency">ความเร่งด่วน *</label><select class="form-select mb-4" name="urgency" id="urgency" required>@foreach(\App\Models\RepairRequest::URGENCIES as $value => $label)<option value="{{ $value }}" @selected(old('urgency', $repair->urgency ?? 'medium') === $value)>{{ $label }}</option>@endforeach</select>
<label class="form-label" for="contact_phone">เบอร์ติดต่อ *</label><input class="form-control" id="contact_phone" name="contact_phone" type="tel" required maxlength="30" value="{{ old('contact_phone', $repair->contact_phone) }}" placeholder="08x xxx xxxx">
<div class="form-text mt-2">ใช้สำหรับติดต่อเกี่ยวกับงานซ่อมนี้</div>
</div></div>
<div class="welcome-card p-4"><h3 class="h6 fw-bold">หลังส่งแจ้งซ่อมแล้ว</h3><p class="small page-lead">คุณจะได้รับเลขอ้างอิง และติดตามสถานะได้จากหน้ารายการแจ้งซ่อม แก้ไขหรือยกเลิกได้ก่อนผู้ดูแลเริ่มดำเนินการ</p><button type="submit" class="btn btn-primary w-100">{{ $repair->exists ? 'บันทึกการแก้ไข' : 'ส่งคำขอแจ้งซ่อม' }} →</button></div>
</div></div>
</form>
@endsection
