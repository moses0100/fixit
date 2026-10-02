<div class="user-dashboard">
    <header class="user-page-heading">
        <div><h1 class="page-title">งานซ่อมของฉัน</h1><p class="page-lead mb-0">สวัสดี {{ auth()->user()->name }} ติดตามอุปกรณ์ของคุณได้ที่นี่</p></div>
        <a class="btn btn-primary" href="{{ route('repairs.create') }}">＋ แจ้งซ่อมใหม่</a>
    </header>

    <section class="user-section" aria-labelledby="active-repairs-title">
        <div class="user-section-heading"><div><h2 id="active-repairs-title">งานที่กำลังดำเนินการ</h2><p class="small text-muted mb-0">รอตรวจสอบ {{ $counts['pending'] ?? 0 }} · กำลังซ่อม {{ $counts['repairing'] ?? 0 }}</p></div><a href="{{ route('repairs.index') }}">ดูรายการทั้งหมด →</a></div>
        @if($activeRepairs->isNotEmpty())
            @include('repairs.cards', ['repairs' => $activeRepairs, 'showProgress' => true])
            @if(($counts['pending'] ?? 0) + ($counts['repairing'] ?? 0) > $activeRepairs->count())
                <p class="small text-muted mt-3">แสดง {{ $activeRepairs->count() }} งานที่อัปเดตล่าสุด <a href="{{ route('repairs.index') }}">ดูงานอื่น ๆ</a></p>
            @endif
        @else
            <div class="user-empty"><span aria-hidden="true">✓</span><div><h3>ไม่มีงานซ่อมค้างอยู่</h3><p>หากอุปกรณ์มีปัญหา กดแจ้งซ่อมใหม่เพื่อให้ผู้ดูแลช่วยตรวจสอบ</p></div></div>
        @endif
    </section>

    <section class="user-section" aria-labelledby="recent-repairs-title">
        <div class="user-section-heading"><h2 id="recent-repairs-title">ประวัติงานล่าสุด</h2><a href="{{ route('repairs.index') }}">ดูประวัติทั้งหมด →</a></div>
        @include('repairs.cards', ['repairs' => $repairs])
    </section>

    <section class="user-section user-summary" aria-labelledby="repair-summary-title">
        <div class="user-section-heading"><h2 id="repair-summary-title">สรุปงานซ่อม</h2><span class="small text-muted">ยอดสถานะอัปเดตอัตโนมัติ</span></div>
        <div class="user-stat-grid">
            @foreach(['all' => 'ทั้งหมด'] + \App\Models\RepairRequest::STATUSES as $status => $label)
                <a class="user-stat" data-count-status="{{ $status }}" href="{{ route('repairs.index', $status === 'all' ? [] : ['status' => $status]) }}"><span class="metric-label">{{ $label }}</span><strong class="metric-value">{{ $status === 'all' ? $counts->sum() : ($counts[$status] ?? 0) }}</strong></a>
            @endforeach
        </div>
        @php($chartCounts = ['pending' => (int) ($counts['pending'] ?? 0), 'repairing' => (int) ($counts['repairing'] ?? 0), 'completed' => (int) ($counts['completed'] ?? 0), 'cancelled' => (int) ($counts['cancelled'] ?? 0)])
        <details class="user-chart-details"><summary>ดูสัดส่วนงานซ่อม</summary><div class="user-chart"><canvas id="statusChart" data-counts='@json($chartCounts)' data-counts-url="{{ route('dashboard.counts') }}" role="img" aria-label="กราฟสัดส่วนงานซ่อม" height="180"></canvas></div><p id="statusSummary" class="small text-muted mb-0"></p></details>
    </section>
</div>
