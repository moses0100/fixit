@extends('layouts.fixit')
@section('title', 'การแจ้งเตือน')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap">
    <div><div class="eyebrow">ACTIVITY CENTER</div><h1 class="page-title">การแจ้งเตือน</h1><p class="page-lead mb-0">ติดตามการเปลี่ยนแปลงของงานซ่อมที่เกี่ยวข้องกับคุณ</p></div>
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-outline-secondary" type="submit">อ่านทั้งหมดแล้ว</button></form>
    @endif
</div>
<div class="panel">
    <div class="panel-head"><h2>รายการล่าสุด</h2><span class="small text-muted">{{ $notifications->total() }} รายการ</span></div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php($data = $notification->data)
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="notification-row {{ $notification->read_at ? '' : 'notification-unread' }}">
                @csrf
                <button type="submit" class="notification-link text-start w-100 border-0 bg-transparent p-0">
                    <div class="d-flex justify-content-between gap-3"><strong>{{ $data['message'] ?? 'มีการอัปเดตใหม่' }}</strong><span class="small text-muted text-nowrap">{{ $notification->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</span></div>
                    @if(!empty($data['title']))<div class="small text-muted mt-1">{{ $data['ticket_no'] ?? '' }} · {{ $data['title'] }}</div>@endif
                    @if(!empty($data['note']))<div class="small mt-2">หมายเหตุ: {{ $data['note'] }}</div>@endif
                </button>
            </form>
        @empty
            <div class="empty"><span class="empty-icon">♢</span><h3 class="h6">ยังไม่มีการแจ้งเตือน</h3><p>เมื่อมีความคืบหน้าของงาน รายการจะแสดงที่นี่</p></div>
        @endforelse
    </div>
    @if($notifications->hasPages())<div class="p-4">{{ $notifications->links() }}</div>@endif
</div>
@endsection
