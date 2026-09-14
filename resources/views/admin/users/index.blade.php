@extends('layouts.fixit')
@section('title', 'จัดการสมาชิก')
@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
    <div>
        <div class="eyebrow">MEMBER MANAGEMENT</div>
        <h1 class="page-title">จัดการสมาชิก</h1>
        <p class="page-lead mb-0">ค้นหาบัญชี ตรวจข้อมูล และช่วยเหลือสมาชิกที่เข้าใช้งานไม่ได้</p>
    </div>
</div>
<div class="panel">
    <form method="GET" class="filters row g-3 m-0" action="{{ route('admin.users.index') }}">
        <div class="col-lg-5">
            <label class="form-label" for="q">ค้นหาสมาชิก</label>
            <input class="form-control" id="q" name="q" value="{{ request('q') }}" maxlength="150" placeholder="ชื่อ / อีเมล / เบอร์โทร / เลขแจ้งซ่อม">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="role">ประเภทบัญชี</label>
            <select class="form-select" id="role" name="role">
                <option value="">ทั้งหมด</option>
                <option value="user" @selected(request('role') === 'user')>สมาชิก</option>
                <option value="admin" @selected(request('role') === 'admin')>ผู้ดูแลระบบ</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="status">สถานะบัญชี</label>
            <select class="form-select" id="status" name="status">
                <option value="">ทั้งหมด</option>
                <option value="active" @selected(request('status') === 'active')>ใช้งานได้</option>
                <option value="suspended" @selected(request('status') === 'suspended')>ถูกระงับ</option>
            </select>
        </div>
        <div class="col-lg-3 d-flex gap-2 align-items-end">
            <button class="btn btn-primary" type="submit">ค้นหา</button>
            <a class="btn btn-light" href="{{ route('admin.users.index') }}">ล้าง</a>
        </div>
    </form>
    <div class="panel-head"><h2>บัญชีทั้งหมด <span class="text-muted fw-normal small">/ {{ $users->total() }} บัญชี</span></h2></div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>ชื่อสมาชิก</th><th>ประเภท</th><th>สถานะ</th><th>งานแจ้งซ่อม</th><th>วันที่สมัคร</th><th><span class="visually-hidden">รายละเอียด</span></th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td><a class="fw-semibold" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a><div class="small-detail">{{ $user->email }}</div></td>
                    <td>{{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'สมาชิก' }}</td>
                    <td><span class="status status-{{ $user->is_active ? 'completed' : 'cancelled' }}">{{ $user->is_active ? 'ใช้งานได้' : 'ถูกระงับ' }}</span></td>
                    <td>{{ $user->repair_requests_count }} รายการ</td>
                    <td class="small text-muted text-nowrap">{{ $user->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}</td>
                    <td><a class="btn btn-sm btn-light" aria-label="ดูข้อมูล {{ $user->name }}" href="{{ route('admin.users.show', $user) }}">↗</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><span class="empty-icon">♙</span><h3 class="h6">ไม่พบสมาชิก</h3><p>ลองเปลี่ยนคำค้นหาหรือตัวกรองอีกครั้ง</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="p-4">{{ $users->links() }}</div>@endif
</div>
@endsection
