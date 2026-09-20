<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบแจ้งซ่อมคอมพิวเตอร์') · FixIT</title>
    @vite(['resources/css/fixit.css', 'resources/js/fixit.js'])
</head>
<body>
@auth
<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">+</span> FixIT<span style="color:#8ba583">.</span></a>
    <div class="small-detail mt-2">COMPUTER REPAIR SYSTEM</div>
    <nav aria-label="เมนูหลัก">
        <div class="sidebar-caption">WORKSPACE</div>
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-symbol">▦</span> ภาพรวมของฉัน</a>
        <a class="nav-item {{ request()->routeIs('repairs.index','repairs.show','repairs.edit') ? 'active' : '' }}" href="{{ route('repairs.index') }}"><span class="nav-symbol">▤</span> รายการแจ้งซ่อม</a>
        <a class="nav-item {{ request()->routeIs('repairs.create') ? 'active' : '' }}" href="{{ route('repairs.create') }}"><span class="nav-symbol">＋</span> แจ้งซ่อมใหม่</a>
        <a class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><span class="nav-symbol">♢</span> การแจ้งเตือน @if(auth()->user()->unreadNotifications()->count())<span class="badge rounded-pill text-bg-danger ms-auto">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a>
        @if(auth()->user()->role === 'admin')
            <div class="sidebar-caption">ADMINISTRATION</div>
            <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-symbol">◈</span> ภาพรวมผู้ดูแล</a>
            <a class="nav-item {{ request()->routeIs('admin.repairs.*') ? 'active' : '' }}" href="{{ route('admin.repairs.index') }}"><span class="nav-symbol">▥</span> จัดการงานซ่อม</a>
            <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nav-symbol">♙</span> จัดการสมาชิก</a>
        @endif
    </nav>
    <div class="sidebar-bottom"><div class="small-detail">แจ้งปัญหา · ติดตาม · กลับมาใช้งาน</div><p class="small-detail mb-0">ทุกงานซ่อมอยู่ในที่เดียว</p></div>
</aside>
<div class="workspace">
    <header class="topbar">
        <div><strong>ศูนย์บริการแจ้งซ่อม</strong><div class="subtitle">ดูแลอุปกรณ์ ให้พร้อมสำหรับทุกวัน</div></div>
        <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="d-none d-sm-inline">{{ auth()->user()->name }}</span></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">ข้อมูลบัญชี</a></li>
                <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">ออกจากระบบ</button></form></li>
            </ul>
        </div>
    </header>
    <main class="content">
@else
<nav class="guest-nav"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">+</span> FixIT.</a><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('login') }}">เข้าสู่ระบบ</a><a class="btn btn-primary" href="{{ route('register') }}">เริ่มต้นใช้งาน</a></div></nav>
<main>
@endauth
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('status'))<div class="alert alert-info mx-auto" style="max-width:700px" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mx-auto" style="max-width:1000px" role="alert"><strong>กรุณาตรวจสอบข้อมูลอีกครั้ง</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
@auth
    <footer class="footer"><span>FixIT · ระบบแจ้งซ่อมและติดตามสถานะคอมพิวเตอร์</span><span>Laravel Term Project / {{ date('Y') }}</span></footer>
@endauth
</main>
@auth</div>@endauth
</body>
</html>
