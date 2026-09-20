@extends('layouts.fixit')
@section('title', $repair->ticket_no)
@section('content')
<a class="small" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">← กลับไปรายการแจ้งซ่อม</a>
<div class="d-flex justify-content-between gap-3 align-items-start mt-4 mb-4 flex-wrap"><div><div class="eyebrow">{{ $repair->ticket_no }}</div><h1 class="page-title">{{ $repair->title }}</h1><p class="text-muted small mb-0">แจ้งเมื่อ {{ $repair->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.</p></div><span class="status status-{{ $repair->status }}">{{ \App\Models\RepairRequest::STATUSES[$repair->status] }}</span></div>
<div class="row g-4"><div class="col-lg-8"><div class="panel mb-4"><div class="panel-head"><h2>ข้อมูลการแจ้งซ่อม</h2>@if(!$admin && $repair->status === 'pending')<a class="btn btn-outline-secondary btn-sm" href="{{ route('repairs.edit', $repair) }}">แก้ไขข้อมูล</a>@endif</div><div class="panel-body">
<div class="row g-4 mb-4">@foreach(['device_type' => 'ประเภทอุปกรณ์', 'brand' => 'ยี่ห้อ', 'model' => 'รุ่น', 'serial_number' => 'Serial Number'] as $field => $label)<div class="col-sm-6"><div class="small text-muted mb-2">{{ $label }}</div><strong>{{ $repair->$field ?: 'ไม่ได้ระบุ' }}</strong></div>@endforeach</div>
<hr class="border-secondary-subtle"><h3 class="h6 mt-4">รายละเอียดอาการ</h3><p class="detail-text mt-3">{{ $repair->problem_description }}</p>
@if($repair->image)<h3 class="h6 mt-4">รูปภาพประกอบ</h3><a href="{{ route('repairs.image', $repair) }}" target="_blank" rel="noopener"><img class="attachment mt-3" src="{{ route('repairs.image', $repair) }}" alt="ภาพอาการเสียของ {{ $repair->brand }}"></a>@endif
</div></div>
<div class="panel mb-4"><div class="panel-head"><h2>ประวัติการดำเนินงาน</h2><span class="small text-muted">{{ $repair->histories->count() }} เหตุการณ์</span></div><div class="panel-body"><div class="timeline">
@foreach($repair->histories as $history)<div class="timeline-item"><div class="d-flex justify-content-between gap-3"><strong>{{ \App\Models\RepairRequest::STATUSES[$history->status_to] ?? $history->status_to }}</strong><span class="small text-muted text-nowrap">{{ $history->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</span></div><div class="small text-muted mt-1">โดย {{ $history->user?->name ?? 'ระบบ' }}@if($history->status_from && $history->status_from !== $history->status_to) · {{ \App\Models\RepairRequest::STATUSES[$history->status_from] ?? $history->status_from }} → {{ \App\Models\RepairRequest::STATUSES[$history->status_to] ?? $history->status_to }}@endif</div>@if($history->note)<p class="small mb-0 mt-2">{{ $history->note }}</p>@endif</div>@endforeach
</div></div></div>
@if($repair->device)<div class="panel mb-4"><div class="panel-head"><h2>ประวัติอุปกรณ์นี้</h2><span class="small text-muted">{{ $repair->device->display_name }}</span></div><div class="panel-body"><div class="small text-muted mb-3">{{ $repair->device->device_type }} · {{ $repair->device->serial_number ?: 'ไม่มี Serial Number' }}</div>@if($deviceHistory->isNotEmpty())<div class="table-responsive"><table class="table table-hover"><thead><tr><th>เลขแจ้งซ่อม</th><th>หัวข้อ</th><th>สถานะ</th><th>วันที่แจ้ง</th></tr></thead><tbody>@foreach($deviceHistory as $deviceRepair)<tr><td><a class="ticket" href="{{ route($admin ? 'admin.repairs.show' : 'repairs.show', $deviceRepair) }}">{{ $deviceRepair->ticket_no }}</a></td><td>{{ $deviceRepair->title }}</td><td><span class="status status-{{ $deviceRepair->status }}">{{ \App\Models\RepairRequest::STATUSES[$deviceRepair->status] }}</span></td><td class="small text-muted text-nowrap">{{ $deviceRepair->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}</td></tr>@endforeach</tbody></table></div>@else<p class="text-muted mb-0">ยังไม่มีประวัติการแจ้งซ่อมรายการอื่นของอุปกรณ์นี้</p>@endif</div></div>@endif
<div class="panel"><div class="panel-head"><h2>หมายเหตุจากผู้ดูแล</h2></div><div class="panel-body"><p class="detail-text mb-0">{{ $repair->admin_note ?: 'ยังไม่มีหมายเหตุ ผู้ดูแลจะอัปเดตข้อมูลหลังตรวจสอบอุปกรณ์' }}</p></div></div>
</div><div class="col-lg-4">
<div class="panel mb-4"><div class="panel-head"><h2>ความคืบหน้า</h2></div><div class="panel-body">
@if($repair->status === 'cancelled')<p class="text-muted">รายการนี้ถูกยกเลิกแล้ว</p>@else
<div class="steps">@foreach(['pending' => 'รับคำขอ', 'repairing' => 'กำลังซ่อม', 'completed' => 'เสร็จสิ้น'] as $step => $label)<div class="step {{ array_search($repair->status, ['pending','repairing','completed']) >= $loop->index ? 'done' : '' }}">{{ $loop->iteration }}. {{ $label }}</div>@endforeach</div>
@endif
@if($repair->completed_at)<p class="small text-muted">ซ่อมเสร็จเมื่อ {{ $repair->completed_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.</p>@endif
<dl class="mb-0"><dt class="small text-muted mb-1">ผู้แจ้ง</dt><dd>{{ $repair->user->name }}</dd><dt class="small text-muted mb-1">เบอร์ติดต่อ</dt><dd>{{ $repair->contact_phone }}</dd><dt class="small text-muted mb-1">ความเร่งด่วน</dt><dd>{{ \App\Models\RepairRequest::URGENCIES[$repair->urgency] }}</dd></dl>
</div></div>
@if($admin)
<div class="panel"><div class="panel-head"><h2>จัดการงานซ่อม</h2></div><form class="panel-body" method="POST" action="{{ route('admin.repairs.status', $repair) }}">@csrf @method('PUT')
<label for="status" class="form-label">สถานะ</label><select name="status" id="status" class="form-select mb-3">@foreach(array_merge([$repair->status], \App\Models\RepairRequest::TRANSITIONS[$repair->status]) as $status)<option value="{{ $status }}" @selected(old('status', $repair->status) === $status)>{{ \App\Models\RepairRequest::STATUSES[$status] }}</option>@endforeach</select>
<label for="admin_note" class="form-label">หมายเหตุ / ผลการซ่อม</label><textarea id="admin_note" name="admin_note" class="form-control mb-3" rows="5" maxlength="5000">{{ old('admin_note', $repair->admin_note) }}</textarea><p class="form-text">ผู้แจ้งจะเห็นหมายเหตุนี้ในหน้ารายละเอียด</p><button class="btn btn-primary w-100" type="submit">บันทึกการดำเนินงาน</button>
</form></div>
@elseif($repair->status === 'pending')
<form class="panel panel-body" method="POST" action="{{ route('repairs.cancel', $repair) }}" data-confirm="ยืนยันยกเลิกรายการนี้? หลังยกเลิกจะเปิดรายการเดิมอีกไม่ได้">@csrf @method('PATCH')<h3 class="h6">ไม่ต้องการส่งซ่อมแล้ว?</h3><p class="small text-muted">ยกเลิกได้ในระหว่างรอตรวจสอบเท่านั้น</p><button class="btn btn-outline-danger" type="submit">ยกเลิกรายการแจ้งซ่อม</button></form>
@endif
</div></div>
@endsection
