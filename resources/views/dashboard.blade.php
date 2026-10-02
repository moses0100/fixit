@extends('layouts.fixit')
@section('title', $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน')
@section('content')
@if(!$admin)
@include('partials.user-dashboard')
@else
<div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap"><div><div class="eyebrow">{{ $admin ? 'ADMIN WORKSPACE' : 'MY WORKSPACE' }}</div><h1 class="page-title">{{ $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน' }}</h1><p class="page-lead mb-0">{{ $admin ? 'ติดตามทุกงานซ่อม และดูแลให้ทุกอุปกรณ์กลับมาพร้อมใช้งาน' : 'ติดตามทุกการแจ้งซ่อมของคุณได้ในที่เดียว' }}</p></div><span class="small text-muted pt-2">{{ now()->timezone('Asia/Bangkok')->format('d / m / Y') }}</span></div>
<div class="welcome-card d-flex align-items-center justify-content-between gap-4 mb-4"><div><div class="eyebrow">LET'S GET IT FIXED</div><h2>สวัสดี {{ auth()->user()->name }}<br>{{ $admin ? 'วันนี้มีอะไรให้เราดูแลบ้าง?' : 'มีปัญหากับอุปกรณ์ใช่ไหม?' }}</h2><p class="page-lead">{{ $admin ? 'เริ่มจากรายการที่รอตรวจสอบ แล้วอัปเดตความคืบหน้าให้ผู้แจ้งทราบ' : 'แจ้งรายละเอียด แนบรูป แล้วติดตามความคืบหน้าได้เลย' }}</p>@if($admin)<a class="btn btn-primary" href="{{ route('admin.repairs.index', ['status' => 'pending']) }}">ดูงานที่รอตรวจสอบ →</a>@endif</div><div class="welcome-illustration">@include('partials.computer')</div></div>
@php($pendingCount = $counts['pending'] ?? 0)
@php($repairingCount = $counts['repairing'] ?? 0)
@if(!$admin)
<div class="action-strip mb-4"><a class="btn btn-primary btn-lg" href="{{ route('repairs.create') }}">＋ แจ้งซ่อมใหม่</a>@if($pendingCount + $repairingCount)<span class="small">รอตรวจสอบ <a class="fw-bold" href="{{ route('repairs.index', ['status' => 'pending']) }}">{{ $pendingCount }}</a> · กำลังซ่อม <a class="fw-bold" href="{{ route('repairs.index', ['status' => 'repairing']) }}">{{ $repairingCount }}</a></span>@else<span class="small text-muted">ยังไม่มีงานค้าง แจ้งซ่อมได้เลยเมื่ออุปกรณ์มีปัญหา</span>@endif</div>
@endif
<div class="metric-grid mb-4">
@foreach(['all' => 'รายการทั้งหมด'] + \App\Models\RepairRequest::STATUSES as $status => $label)
<div class="metric-cell"><a class="d-block metric text-decoration-none" data-count-status="{{ $status }}" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index', $status === 'all' ? [] : ['status' => $status]) }}"><span class="metric-icon">{{ ['all'=>'▤','pending'=>'◷','repairing'=>'⚒','completed'=>'✓','cancelled'=>'−'][$status] }}</span><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ $status === 'all' ? $counts->sum() : ($counts[$status] ?? 0) }}</div><span class="small-detail">รายการ</span></a></div>
@endforeach
</div>
@php($chartCounts = ['pending' => (int) ($counts['pending'] ?? 0), 'repairing' => (int) ($counts['repairing'] ?? 0), 'completed' => (int) ($counts['completed'] ?? 0), 'cancelled' => (int) ($counts['cancelled'] ?? 0)])
<div class="row g-3 mb-4">
<div class="col-lg-4"><div class="panel h-100"><div class="panel-head"><h2>สัดส่วนงานซ่อม</h2><span class="small text-muted">ภาพรวมสถานะ</span></div><div class="panel-body"><canvas id="statusChart" data-counts='@json($chartCounts)' data-counts-url="{{ route($admin ? 'admin.dashboard.counts' : 'dashboard.counts') }}" role="img" aria-label="กราฟสัดส่วนงานซ่อม" height="220"></canvas><p class="small text-muted mt-3 mb-0" id="statusSummary"></p></div></div></div>
<div class="col-lg-8"><div class="panel h-100"><div class="panel-head"><h2>รายการแจ้งซ่อมล่าสุด</h2><a class="small fw-semibold" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">ดูทั้งหมด →</a></div>@include('repairs.table')</div></div>
</div>
@endif
@endsection
