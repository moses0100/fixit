<div class="repair-card-grid">
    @forelse($repairs as $repair)
        @php($latestHistory = $repair->histories->first())
        <article class="repair-card" aria-label="ใบงาน {{ $repair->ticket_no }}">
            <div class="repair-card-top"><span class="ticket">{{ $repair->ticket_no }}</span><span class="status status-{{ $repair->status }}">{{ \App\Models\RepairRequest::STATUSES[$repair->status] }}</span></div>
            <h3><a href="{{ route('repairs.show', $repair) }}">{{ $repair->title }}</a></h3>
            <p class="repair-device">{{ $repair->brand }} {{ $repair->model }} <span class="text-muted">· {{ ['Notebook' => 'โน้ตบุ๊ก', 'Desktop PC' => 'คอมพิวเตอร์ตั้งโต๊ะ', 'All-in-One' => 'คอมพิวเตอร์ออลอินวัน', 'Monitor' => 'จอภาพ', 'Other' => 'อุปกรณ์อื่น ๆ'][$repair->device_type] ?? $repair->device_type }}</span></p>
            @if($showProgress ?? false)
                <ol class="repair-progress" aria-label="ขั้นตอนงานซ่อม">
                    @foreach(['pending' => 'รับเรื่อง', 'repairing' => 'กำลังซ่อม', 'completed' => 'ซ่อมเสร็จ'] as $step => $label)
                        <li class="{{ $repair->status === $step ? 'current' : ($step === 'pending' ? 'done' : '') }}" @if($repair->status === $step) aria-current="step" @endif>{{ $label }}</li>
                    @endforeach
                </ol>
            @endif
            <div class="repair-update"><span class="small text-muted">ความคืบหน้าล่าสุด</span><p>{{ $latestHistory?->note ?: ($repair->admin_note ?: 'ส่งคำขอแล้ว รอผู้ดูแลตรวจสอบ') }}</p><time class="small text-muted" datetime="{{ ($latestHistory?->created_at ?? $repair->updated_at)->toIso8601String() }}">{{ ($latestHistory?->created_at ?? $repair->updated_at)->timezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.</time></div>
            <div class="repair-card-bottom"><span class="small text-muted">ความเร่งด่วน <strong class="{{ $repair->urgency === 'high' ? 'urgency-high' : '' }}">{{ \App\Models\RepairRequest::URGENCIES[$repair->urgency] }}</strong></span><a class="btn btn-outline-secondary btn-sm" aria-label="ดูรายละเอียด {{ $repair->ticket_no }}" href="{{ route('repairs.show', $repair) }}">ดูรายละเอียด →</a></div>
        </article>
    @empty
        <div class="user-empty"><div><h3>ยังไม่มีรายการในส่วนนี้</h3><p>หากกำลังค้นหา ลองเปลี่ยนตัวกรอง หรือเริ่มแจ้งซ่อมเมื่ออุปกรณ์มีปัญหา</p><a href="{{ route('repairs.create') }}">แจ้งซ่อมใหม่ →</a></div></div>
    @endforelse
</div>
