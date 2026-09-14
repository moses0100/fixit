@extends('layouts.fixit')
@section('title', 'ข้อมูลสมาชิก '.$user->name)
@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
    <div>
        <div class="eyebrow">MEMBER PROFILE</div>
        <h1 class="page-title">{{ $user->name }}</h1>
        <p class="page-lead mb-0">{{ $user->email }}</p>
    </div>
    <a class="btn btn-light" href="{{ route('admin.users.index') }}">← กลับไปรายชื่อสมาชิก</a>
</div>
<div class="row g-4">
    <div class="col-xl-7">
        <div class="panel mb-4">
            <div class="panel-head"><h2>ข้อมูลบัญชี</h2><span class="status status-{{ $user->is_active ? 'completed' : 'cancelled' }}">{{ $user->is_active ? 'ใช้งานได้' : 'ถูกระงับ' }}</span></div>
            <div class="panel-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6"><div class="small-detail">ประเภทบัญชี</div><strong>{{ $user->role === 'admin' ? 'ผู้ดูแลระบบ' : 'สมาชิกทั่วไป' }}</strong></div>
                    <div class="col-sm-6"><div class="small-detail">รายการแจ้งซ่อมทั้งหมด</div><strong>{{ $user->repair_requests_count }} รายการ</strong></div>
                    <div class="col-sm-6"><div class="small-detail">วันที่สมัคร</div><strong>{{ $user->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</strong></div>
                    <div class="col-sm-6"><div class="small-detail">แก้ไขล่าสุด</div><strong>{{ $user->updated_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</strong></div>
                </div>
                @if($user->role === 'user')
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="row g-3">
                    @csrf @method('PUT')
                    <div class="col-md-6"><label class="form-label" for="name">ชื่อ</label><input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"></div>
                    <div class="col-md-6"><label class="form-label" for="email">อีเมล</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"></div>
                    <div><button class="btn btn-primary" type="submit">บันทึกข้อมูลสมาชิก</button></div>
                </form>
                @else
                <div class="alert alert-info mb-0">บัญชีผู้ดูแลระบบดูข้อมูลได้ แต่ต้องแก้ไขผ่านผู้ดูแลเซิร์ฟเวอร์เพื่อป้องกันการยึดบัญชี</div>
                @endif
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h2>รายการแจ้งซ่อมล่าสุด</h2>@if($user->repair_requests_count)<a class="small fw-semibold" href="{{ route('admin.repairs.index', ['q' => $user->email]) }}">ค้นหารายการทั้งหมด →</a>@endif</div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>เลขแจ้งซ่อม</th><th>หัวข้อ</th><th>สถานะ</th><th>วันที่แจ้ง</th></tr></thead>
                    <tbody>
                    @forelse($repairs as $repair)
                        <tr><td><a class="ticket" href="{{ route('admin.repairs.show', $repair) }}">{{ $repair->ticket_no }}</a></td><td>{{ $repair->title }}</td><td><span class="status status-{{ $repair->status }}">{{ \App\Models\RepairRequest::STATUSES[$repair->status] }}</span></td><td class="small text-muted text-nowrap">{{ $repair->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}</td></tr>
                    @empty
                        <tr><td colspan="4"><div class="empty py-4">สมาชิกคนนี้ยังไม่มีรายการแจ้งซ่อม</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        @if($user->role === 'user')
        <div class="panel mb-4">
            <div class="panel-head"><h2>ช่วยเหลือการเข้าสู่ระบบ</h2></div>
            <div class="panel-body">
                <p class="page-lead">ส่งลิงก์ตั้งรหัสผ่านใหม่ไปยังอีเมลปัจจุบันของสมาชิก</p>
                <form method="POST" action="{{ route('admin.users.reset-link', $user) }}">@csrf<button class="btn btn-outline-secondary" type="submit" @disabled(! $user->is_active)>ส่งลิงก์ตั้งรหัสผ่านใหม่</button></form>
                <hr class="my-4">
                <p class="page-lead">หากสมาชิกเข้าอีเมลไม่ได้ ให้ตั้งรหัสผ่านชั่วคราวและแจ้งผ่านช่องทางที่ยืนยันตัวตนแล้ว</p>
                <form method="POST" action="{{ route('admin.users.password', $user) }}" class="row g-3">
                    @csrf @method('PUT')
                    <div><label class="form-label" for="password">รหัสผ่านชั่วคราว</label><input class="form-control" type="password" id="password" name="password" required autocomplete="new-password" minlength="8"></div>
                    <div><label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านชั่วคราว</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"></div>
                    <div><button class="btn btn-primary" type="submit">ตั้งรหัสผ่านชั่วคราว</button></div>
                </form>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head"><h2>สถานะบัญชี</h2></div>
            <div class="panel-body">
                <p class="page-lead">{{ $user->is_active ? 'เมื่อระงับ สมาชิกจะออกจากระบบทุกอุปกรณ์และไม่สามารถเข้าสู่ระบบได้' : 'เมื่อเปิดใช้งาน สมาชิกจะกลับมาเข้าสู่ระบบได้ตามปกติ' }}</p>
                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                    <button class="btn {{ $user->is_active ? 'btn-outline-danger' : 'btn-primary' }}" type="submit" onclick="return confirm('{{ $user->is_active ? 'ยืนยันการระงับบัญชีนี้?' : 'ยืนยันการเปิดใช้งานบัญชีนี้?' }}')">{{ $user->is_active ? 'ระงับบัญชี' : 'เปิดใช้งานบัญชี' }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
