<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ใบรับซ่อม {{ $repair->ticket_no }} · FixIT</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#eee;font-family:"Leelawadee UI",Tahoma,sans-serif;color:#1a3532}
.slip{max-width:760px;margin:24px auto;background:#fff;border-radius:12px;overflow:hidden}
.slip-head{background:#21695b;color:#fff;padding:22px 28px;display:flex;justify-content:space-between;align-items:center}
.slip-body{padding:28px}
.meta{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
.qr{border:1px dashed #bbb;border-radius:10px;padding:10px;text-align:center}
@media print{body{background:#fff}.slip{margin:0;max-width:none;border-radius:0}.no-print{display:none!important}}
</style>
</head>
<body>
<div class="slip">
<div class="slip-head"><div><strong style="font-size:22px">FixIT · ใบรับซ่อม</strong><div class="small">{{ $repair->ticket_no }}</div></div><div class="text-end small">ศูนย์บริการแจ้งซ่อม<br>{{ now()->timezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.</div></div>
<div class="slip-body">
<div class="meta mb-3"><div><div class="text-muted small">ผู้แจ้ง</div><strong>{{ $repair->user->name }}</strong><div class="small text-muted">{{ $repair->contact_phone }}</div></div><div class="text-end"><div class="text-muted small">สถานะ</div><strong>{{ \App\Models\RepairRequest::STATUSES[$repair->status] }}</strong><div class="small text-muted">{{ \App\Models\RepairRequest::URGENCIES[$repair->urgency] }} · แจ้ง {{ $repair->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</div></div></div>
<hr>
<p class="mb-1"><strong>{{ $repair->title }}</strong></p>
<p class="text-muted">{{ $repair->device_type }} {{ $repair->brand }} {{ $repair->model }} · SN: {{ $repair->serial_number ?: '-' }}</p>
<p style="white-space:pre-wrap">{{ $repair->problem_description }}</p>
@if($repair->admin_note)<div class="alert alert-light border">หมายเหตุช่าง: {{ $repair->admin_note }}</div>@endif
<div class="d-flex justify-content-between align-items-center mt-4 gap-3 flex-wrap">
<div class="qr"><img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode(route('repairs.show', $repair)) }}" alt="QR ติดตามงาน {{ $repair->ticket_no }}" width="130" height="130"><div class="small mt-1">{{ $repair->ticket_no }}<br>สแกนเพื่อติดตามสถานะ</div></div>
<div class="small text-muted">ลงชื่อผู้รับเรื่อง ........................<br><br>ลงชื่อผู้ส่งซ่อม ........................</div>
</div>
<div class="mt-4 no-print d-flex gap-2"><button class="btn btn-primary" onclick="window.print()">พิมพ์ใบรับซ่อม</button><a class="btn btn-outline-secondary" href="{{ url()->previous() }}">กลับ</a></div>
</div>
</div>
</body>
</html>
