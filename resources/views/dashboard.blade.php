@extends('layouts.fixit')
@section('title', $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap"><div><div class="eyebrow">{{ $admin ? 'ADMIN WORKSPACE' : 'MY WORKSPACE' }}</div><h1 class="page-title">{{ $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน' }}</h1><p class="page-lead mb-0">{{ $admin ? 'ติดตามทุกงานซ่อม และดูแลให้ทุกอุปกรณ์กลับมาพร้อมใช้งาน' : 'ติดตามทุกการแจ้งซ่อมของคุณได้ในที่เดียว' }}</p></div><span class="small text-muted pt-2">{{ now()->timezone('Asia/Bangkok')->format('d / m / Y') }}</span></div>
<div class="welcome-card d-flex align-items-center justify-content-between gap-4 mb-4"><div><div class="eyebrow">LET'S GET IT FIXED</div><h2>สวัสดี {{ auth()->user()->name }}<br>{{ $admin ? 'วันนี้มีอะไรให้เราดูแลบ้าง?' : 'มีปัญหากับอุปกรณ์ใช่ไหม?' }}</h2><p class="page-lead">{{ $admin ? 'เริ่มจากรายการที่รอตรวจสอบ แล้วอัปเดตความคืบหน้าให้ผู้แจ้งทราบ' : 'แจ้งรายละเอียด แนบรูป แล้วติดตามความคืบหน้าได้เลย' }}</p><a class="btn btn-primary" href="{{ $admin ? route('admin.repairs.index', ['status' => 'pending']) : route('repairs.create') }}">{{ $admin ? 'ดูงานที่รอตรวจสอบ' : '＋ แจ้งซ่อมใหม่' }} →</a></div><div class="welcome-illustration">@include('partials.computer')</div></div>
<div class="row g-3 mb-4">
@foreach(['all' => 'รายการทั้งหมด'] + \App\Models\RepairRequest::STATUSES as $status => $label)
<div class="col-6 col-xl"><a class="d-block metric text-decoration-none" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index', $status === 'all' ? [] : ['status' => $status]) }}"><span class="metric-icon">{{ ['all'=>'▤','pending'=>'◷','repairing'=>'⚒','completed'=>'✓','cancelled'=>'−'][$status] }}</span><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ $status === 'all' ? $counts->sum() : ($counts[$status] ?? 0) }}</div><span class="small-detail">รายการ</span></a></div>
@endforeach
</div>
<div class="panel"><div class="panel-head"><h2>รายการแจ้งซ่อมล่าสุด</h2><a class="small fw-semibold" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">ดูทั้งหมด →</a></div>@include('repairs.table')</div>
@endsection
