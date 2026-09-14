<div class="table-responsive">
<table class="table table-hover">
    <thead><tr><th>รายการ / เลขแจ้งซ่อม</th><th>อุปกรณ์</th>@if($admin)<th>ผู้แจ้ง</th>@endif<th>ความเร่งด่วน</th><th>สถานะ</th><th>วันที่แจ้ง</th><th><span class="visually-hidden">รายละเอียด</span></th></tr></thead>
    <tbody>
    @forelse($repairs as $repair)
        <tr>
            <td><a class="fw-semibold" href="{{ route($admin ? 'admin.repairs.show' : 'repairs.show', $repair) }}">{{ $repair->title }}</a><div class="ticket mt-1">{{ $repair->ticket_no }}</div></td>
            <td>{{ $repair->brand }}<div class="small-detail">{{ $repair->device_type }} {{ $repair->model }}</div></td>
            @if($admin)<td>{{ $repair->user->name }}</td>@endif
            <td><span class="{{ $repair->urgency === 'high' ? 'urgency-high fw-semibold' : 'text-muted' }}">{{ \App\Models\RepairRequest::URGENCIES[$repair->urgency] }}</span></td>
            <td><span class="status status-{{ $repair->status }}">{{ \App\Models\RepairRequest::STATUSES[$repair->status] }}</span></td>
            <td class="small text-muted text-nowrap">{{ $repair->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}</td>
            <td><a class="btn btn-sm btn-light" aria-label="ดูรายละเอียด {{ $repair->ticket_no }}" href="{{ route($admin ? 'admin.repairs.show' : 'repairs.show', $repair) }}">↗</a></td>
        </tr>
    @empty
        <tr><td colspan="{{ $admin ? 7 : 6 }}"><div class="empty"><span class="empty-icon">▤</span><h3 class="h6">ยังไม่มีรายการในส่วนนี้</h3><p>เมื่อมีรายการแจ้งซ่อม คุณจะติดตามได้จากที่นี่</p><a href="{{ route('repairs.create') }}" class="btn btn-outline-secondary">แจ้งซ่อมรายการแรก</a></div></td></tr>
    @endforelse
    </tbody>
</table>
</div>
