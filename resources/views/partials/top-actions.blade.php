<button class="btn btn-outline-secondary btn-sm" type="button" data-theme-toggle aria-label="สลับโหมดมืด/สว่าง" title="สลับโหมดมืด/สว่าง">🌙</button>
@php($unreadCount = auth()->user()->unreadNotifications()->count())
<div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="dropdown" aria-expanded="false" aria-label="การแจ้งเตือน">🔔@if($unreadCount)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif</button>
    <ul class="dropdown-menu dropdown-menu-end p-0" style="width:320px;max-width:90vw">
        <li class="px-3 py-2 fw-bold border-bottom">การแจ้งเตือน</li>
        @forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $item)
        <li class="{{ $item->read_at ? '' : 'notification-unread' }}"><form method="POST" action="{{ route('notifications.read', $item->id) }}">@csrf<button class="dropdown-item py-2" type="submit"><span class="d-block small fw-semibold">{{ $item->data['message'] ?? 'มีการอัปเดตใหม่' }}</span><span class="d-block small text-muted">{{ $item->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</span></button></form></li>
        @empty
        <li class="px-3 py-3 small text-muted">ยังไม่มีการแจ้งเตือน</li>
        @endforelse
        <li class="border-top"><a class="dropdown-item text-center small fw-semibold py-2" href="{{ route('notifications.index') }}">ดูทั้งหมด</a></li>
    </ul>
</div>
<div class="dropdown">
    <button class="btn d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="d-none d-sm-inline">{{ auth()->user()->name }}</span></button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">ข้อมูลบัญชี</a></li>
        <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">ออกจากระบบ</button></form></li>
    </ul>
</div>
