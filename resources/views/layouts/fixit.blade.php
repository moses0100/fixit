<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบแจ้งซ่อมคอมพิวเตอร์') · FixIT</title>
    <meta name="description" content="FixIT ระบบแจ้งซ่อมและติดตามสถานะคอมพิวเตอร์ แจ้งปัญหา แนบรูป ติดตามความคืบหน้าได้ในที่เดียว">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%231f6f5b'/%3E%3Ctext x='32' y='44' font-size='36' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='bold'%3E%2B%3C/text%3E%3C/svg%3E">
    <script>try{if(localStorage.getItem('fixit-theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
    @vite(['resources/css/fixit.css', 'resources/js/fixit.js'])
</head>
<body class="@auth{{ auth()->user()->role === 'admin' ? 'theme-admin' : '' }}@endauth">
@auth
@php($isAdmin = auth()->user()->role === 'admin')
@if($isAdmin)
<aside class="sidebar admin-side">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">+</span> FixIT Admin</a>
    <div class="small-detail mt-2">ADMIN CONSOLE</div>
    <nav aria-label="เมนูหลัก">
        <div class="sidebar-caption">เมนู</div>
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-symbol">▦</span> ภาพรวมของฉัน</a>
        <a class="nav-item {{ request()->routeIs('repairs.index','repairs.show','repairs.edit') ? 'active' : '' }}" href="{{ route('repairs.index') }}"><span class="nav-symbol">▤</span> รายการแจ้งซ่อม</a>
        <a class="nav-item {{ request()->routeIs('repairs.create') ? 'active' : '' }}" href="{{ route('repairs.create') }}"><span class="nav-symbol">＋</span> แจ้งซ่อมใหม่</a>
        <div class="sidebar-caption">ผู้ดูแลระบบ</div>
        <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-symbol">◈</span> ภาพรวมผู้ดูแล</a>
        <a class="nav-item {{ request()->routeIs('admin.repairs.*') ? 'active' : '' }}" href="{{ route('admin.repairs.index') }}"><span class="nav-symbol">▥</span> จัดการงานซ่อม</a>
        <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nav-symbol">♙</span> จัดการสมาชิก</a>
    </nav>
    <div class="sidebar-bottom"><div class="small-detail">ดูแลทุกงานซ่อมในที่เดียว</div><p class="small-detail mb-0">ตรวจสอบแล้วอัปเดตให้ผู้แจ้งทราบ</p></div>
</aside>
<div class="workspace">
    <header class="topbar">
        <div><strong>ศูนย์ดูแลระบบ</strong><div class="subtitle">ตรวจสอบงาน อัปเดตสถานะ ดูแลสมาชิก</div></div>
        <div class="d-flex align-items-center gap-2">
        @include('partials.top-actions')
        </div>
    </header>
    <main class="content">
@else
<nav class="usernav" aria-label="เมนูหลัก">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">+</span> FixIT</a>
    <div class="usernav-links">
        <a class="usernav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">ภาพรวม</a>
        <a class="usernav-link {{ request()->routeIs('repairs.index','repairs.show','repairs.edit') ? 'active' : '' }}" href="{{ route('repairs.index') }}">รายการของฉัน</a>
    </div>
    <a class="btn btn-primary btn-sm text-nowrap" href="{{ route('repairs.create') }}">＋ แจ้งซ่อม</a>
    <div class="d-flex align-items-center gap-2">
    @include('partials.top-actions')
    </div>
</nav>
<main class="content user-content">
@endif
@else
<nav class="guest-nav"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">+</span> FixIT</a><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('login') }}">เข้าสู่ระบบ</a><a class="btn btn-primary" href="{{ route('register') }}">เริ่มต้นใช้งาน</a></div></nav>
<main>
@endauth
    @if(session('success'))<div class="alert alert-success success-pop" role="status"><span class="success-check">✓</span> {{ session('success') }}</div>@endif
    @if(session('status'))<div class="alert alert-info mx-auto" style="max-width:700px" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mx-auto" style="max-width:1000px" role="alert"><strong>กรุณาตรวจสอบข้อมูลอีกครั้ง</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
@auth
    <footer class="footer"><span>FixIT · ระบบแจ้งซ่อมและติดตามสถานะคอมพิวเตอร์</span><span>Laravel Term Project / {{ date('Y') }}</span></footer>
@else
    <footer class="footer landing-footer"><span>FixIT · แจ้งปัญหา ติดตาม กลับมาใช้งาน</span><span>Laravel Term Project / {{ date('Y') }}</span></footer>
@endauth
</main>
@auth@if(auth()->user()->role === 'admin')</div>@endif@endauth
<div class="send-overlay" data-send-overlay hidden><div class="send-card"><div class="send-spinner"></div><strong>กำลังส่งคำขอแจ้งซ่อม...</strong><span class="small text-muted">กำลังบันทึกข้อมูลและสร้างเลขงาน</span></div></div>
</body>
</html>
