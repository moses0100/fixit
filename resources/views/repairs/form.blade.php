@extends('layouts.fixit')
@section('title', $repair->exists ? 'แก้ไขรายการแจ้งซ่อม' : 'แจ้งซ่อมใหม่')
@section('content')
<a class="small" href="{{ route('repairs.index') }}">← กลับไปรายการแจ้งซ่อม</a>
<div class="eyebrow mt-4">NEW REPAIR / DEVICE SUPPORT</div>
<h1 class="page-title">{{ $repair->exists ? 'แก้ไขรายการแจ้งซ่อม' : 'ให้อุปกรณ์ของคุณกลับมาพร้อมใช้งาน' }}</h1>
<p class="page-lead mb-4">กรอกรายละเอียดให้ครบ เพื่อให้ผู้ดูแลตรวจสอบปัญหาได้ตรงจุด</p>
<form action="{{ $repair->exists ? route('repairs.update', $repair) : route('repairs.store') }}" method="POST" enctype="multipart/form-data" novalidate data-validate>
@csrf
@if($repair->exists) @method('PUT') @endif
<div class="row g-4"><div class="col-lg-8">
<div class="panel mb-4"><div class="panel-head"><h2><span class="section-number">01</span> ข้อมูลอุปกรณ์</h2></div><div class="panel-body row g-3">
<div class="col-md-6"><label class="form-label" for="device_type">ประเภทอุปกรณ์ <span class="req">*</span></label><select class="form-select @error('device_type') is-invalid @enderror" name="device_type" id="device_type" required>@foreach(\App\Models\RepairRequest::DEVICES as $type)<option @selected(old('device_type', $repair->device_type) === $type)>{{ $type }}</option>@endforeach</select>@error('device_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@foreach(['brand' => 'ยี่ห้อ <span class="req">*</span>', 'model' => 'รุ่น', 'serial_number' => 'Serial Number'] as $field => $label)
<div class="col-md-6"><label class="form-label" for="{{ $field }}">{!! $label !!}</label><input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" maxlength="100" value="{{ old($field, $repair->$field) }}" @required($field === 'brand') placeholder="{{ $field === 'brand' ? 'เช่น Acer, Asus, Lenovo' : ($field === 'model' ? 'เช่น Nitro 5, Vivobook' : 'เช่น NHQXXXX') }}">@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
@endforeach
</div></div>
<div class="panel"><div class="panel-head"><h2><span class="section-number">02</span> รายละเอียดปัญหา</h2></div><div class="panel-body">
<label class="form-label" for="title">หัวข้อปัญหา <span class="req">*</span></label><input class="form-control @error('title') is-invalid @enderror mb-1" id="title" name="title" maxlength="150" required value="{{ old('title', $repair->title) }}" placeholder="เช่น เปิดเครื่องไม่ติด หรือหน้าจอไม่แสดงผล" autocomplete="off" data-suggest>@error('title')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
<div class="alert alert-info mt-2 mb-0" data-suggest-box hidden></div>
<label class="form-label mt-3" for="problem_description">อาการที่พบ <span class="req">*</span></label><textarea class="form-control @error('problem_description') is-invalid @enderror mb-1" id="problem_description" name="problem_description" rows="6" maxlength="5000" required placeholder="อธิบายอาการ เกิดขึ้นเมื่อไหร่ และสิ่งที่ได้ลองแก้ไขแล้ว">{{ old('problem_description', $repair->problem_description) }}</textarea>@error('problem_description')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
<label class="form-label mt-3" for="image">รูปภาพประกอบ</label><input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input>@error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<div class="form-text">JPG, PNG หรือ WebP ขนาดไม่เกิน 2 MB · รูปใหม่จะแทนที่รูปเดิม</div>
<img data-image-preview class="attachment mt-3" alt="ตัวอย่างรูปที่เลือก" hidden>
@if($repair->image)<img class="attachment mt-3" src="{{ route('repairs.image', $repair) }}" alt="รูปปัจจุบัน"><label class="form-check mt-3"><input class="form-check-input" type="checkbox" name="remove_image" value="1"><span class="form-check-label">ลบรูปเดิม</span></label>@endif
</div></div>
</div><div class="col-lg-4"><div class="panel mb-4"><div class="panel-head"><h2><span class="section-number">03</span> การติดต่อ</h2></div><div class="panel-body">
<label class="form-label" for="urgency">ความเร่งด่วน <span class="req">*</span></label><select class="form-select @error('urgency') is-invalid @enderror mb-1" name="urgency" id="urgency" required>@foreach(\App\Models\RepairRequest::URGENCIES as $value => $label)<option value="{{ $value }}" @selected(old('urgency', $repair->urgency ?? 'medium') === $value)>{{ $label }}</option>@endforeach</select>@error('urgency')<div class="invalid-feedback d-block mb-3">{{ $message }}</div>@enderror
<label class="form-label mt-3" for="contact_phone">เบอร์ติดต่อ <span class="req">*</span></label><input class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" pattern="[0-9][0-9+() .-]{7,18}[0-9]" value="{{ old('contact_phone', $repair->contact_phone) }}" placeholder="08xxxxxxxx" aria-describedby="phone-help">@error('contact_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
<div class="form-text" id="phone-help">เช่น 0812345678 ใช้ติดต่อเรื่องงานซ่อมนี้</div>
</div></div>
<div class="welcome-card p-4"><h3 class="h6 fw-bold">หลังส่งแจ้งซ่อมแล้ว</h3><p class="small page-lead">คุณจะได้รับเลขอ้างอิง และติดตามสถานะได้จากหน้ารายการแจ้งซ่อม แก้ไขหรือยกเลิกได้ก่อนผู้ดูแลเริ่มดำเนินการ</p><button type="submit" class="btn btn-primary w-100">{{ $repair->exists ? 'บันทึกการแก้ไข' : 'ส่งคำขอแจ้งซ่อม' }} →</button></div>
</div></div>
</form>
@endsection
