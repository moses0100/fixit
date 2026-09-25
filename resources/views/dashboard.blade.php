@extends('layouts.fixit')
@section('title', $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap"><div><div class="eyebrow">{{ $admin ? 'ADMIN WORKSPACE' : 'MY WORKSPACE' }}</div><h1 class="page-title">{{ $admin ? 'ภาพรวมผู้ดูแลระบบ' : 'ภาพรวมของฉัน' }}</h1><p class="page-lead mb-0">{{ $admin ? 'ติดตามทุกงานซ่อม และดูแลให้ทุกอุปกรณ์กลับมาพร้อมใช้งาน' : 'ติดตามทุกการแจ้งซ่อมของคุณได้ในที่เดียว' }}</p></div><span class="small text-muted pt-2">{{ now()->timezone('Asia/Bangkok')->format('d / m / Y') }}</span></div>
<div class="welcome-card d-flex align-items-center justify-content-between gap-4 mb-4"><div><div class="eyebrow">LET'S GET IT FIXED</div><h2>สวัสดี {{ auth()->user()->name }}<br>{{ $admin ? 'วันนี้มีอะไรให้เราดูแลบ้าง?' : 'มีปัญหากับอุปกรณ์ใช่ไหม?' }}</h2><p class="page-lead">{{ $admin ? 'เริ่มจากรายการที่รอตรวจสอบ แล้วอัปเดตความคืบหน้าให้ผู้แจ้งทราบ' : 'แจ้งรายละเอียด แนบรูป แล้วติดตามความคืบหน้าได้เลย' }}</p><a class="btn btn-primary" href="{{ $admin ? route('admin.repairs.index', ['status' => 'pending']) : route('repairs.create') }}">{{ $admin ? 'ดูงานที่รอตรวจสอบ' : '＋ แจ้งซ่อมใหม่' }} →</a></div><div class="welcome-illustration">@include('partials.computer')</div></div>
<div class="row g-3 mb-4">
@foreach(['all' => 'รายการทั้งหมด'] + \App\Models\RepairRequest::STATUSES as $status => $label)
<div class="col-6 col-xl"><a class="d-block metric text-decoration-none" data-count-status="{{ $status }}" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index', $status === 'all' ? [] : ['status' => $status]) }}"><span class="metric-icon">{{ ['all'=>'▤','pending'=>'◷','repairing'=>'⚒','completed'=>'✓','cancelled'=>'−'][$status] }}</span><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ $status === 'all' ? $counts->sum() : ($counts[$status] ?? 0) }}</div><span class="small-detail">รายการ</span></a></div>
@endforeach
</div>
<div class="row g-3 mb-4">
<div class="col-lg-4"><div class="panel h-100"><div class="panel-head"><h2>สัดส่วนงานซ่อม</h2><span class="small text-muted">ภาพรวมสถานะ</span></div><div class="panel-body"><canvas id="statusChart" height="220"></canvas><p class="small text-muted mt-3 mb-0" id="statusSummary"></p></div></div></div>
<div class="col-lg-8"><div class="panel h-100"><div class="panel-head"><h2>รายการแจ้งซ่อมล่าสุด</h2><a class="small fw-semibold" href="{{ route($admin ? 'admin.repairs.index' : 'repairs.index') }}">ดูทั้งหมด →</a></div>@include('repairs.table')</div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@php($chartCounts = ['pending' => (int) ($counts['pending'] ?? 0), 'repairing' => (int) ($counts['repairing'] ?? 0), 'completed' => (int) ($counts['completed'] ?? 0), 'cancelled' => (int) ($counts['cancelled'] ?? 0)])
<script>
(function(){
  var counts = @json($chartCounts);
  var total = (counts.pending||0)+(counts.repairing||0)+(counts.completed||0)+(counts.cancelled||0);
  var el = document.getElementById('statusSummary');
  if (el) el.textContent = total > 0 ? 'ทั้งหมด '+total+' รายการ · ซ่อมเสร็จ '+counts.completed+' · กำลังซ่อม '+counts.repairing+' · รอตรวจ '+counts.pending : 'ยังไม่มีข้อมูลงานซ่อม';
  var cv = document.getElementById('statusChart');
  var chart = (cv && window.Chart) ? new Chart(cv, {type:'doughnut', data:{labels:['รอตรวจสอบ','กำลังซ่อม','ซ่อมเสร็จ','ยกเลิก'], datasets:[{data:[counts.pending,counts.repairing,counts.completed,counts.cancelled], backgroundColor:['#e8b739','#4a8bd4','#2f9e6e','#9aa5a0'], borderWidth:2, borderColor:'#fff'}]}, options:{plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:12}}}}, cutout:'62%'}}) : null;
  function paint(c) {
    document.querySelectorAll('[data-count-status]').forEach(function (a) {
      var v = a.querySelector('.metric-value');
      if (v && c[a.getAttribute('data-count-status')] !== undefined) v.textContent = c[a.getAttribute('data-count-status')];
    });
    if (chart) { chart.data.datasets[0].data = [c.pending, c.repairing, c.completed, c.cancelled]; chart.update(); }
    if (el) { var t = (c.pending||0)+(c.repairing||0)+(c.completed||0)+(c.cancelled||0); el.textContent = t > 0 ? 'ทั้งหมด '+t+' รายการ · ซ่อมเสร็จ '+c.completed+' · กำลังซ่อม '+c.repairing+' · รอตรวจ '+c.pending : 'ยังไม่มีข้อมูลงานซ่อม'; }
  }
  setInterval(function () {
    fetch('/dashboard/counts', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); }).then(paint).catch(function () {});
  }, 15000);
})();
</script>
@endsection
