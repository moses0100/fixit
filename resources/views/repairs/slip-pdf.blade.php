<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ใบรับซ่อม {{ $repair->ticket_no }}</title>
<style>
@page{size:A4;margin:15mm}
body{font-family:"Tahoma",sans-serif;font-size:12px;color:#222}
h1{font-size:20px;margin:0}
.head{border-bottom:3px solid #21695b;padding-bottom:10px;margin-bottom:14px}
table{width:100%;border-collapse:collapse;margin:10px 0}
th,td{border:1px solid #999;padding:6px 8px;text-align:left;vertical-align:top}
th{background:#eee;width:28%}
.sign{margin-top:26px;width:100%}
.sign td{border:none;text-align:center;padding-top:30px}
.small{font-size:11px;color:#555}
</style>
</head>
<body>
<div class="head">
<h1>FixIT - Job Receipt {{ $repair->ticket_no }}</h1>
<div class="small">Computer Repair Request Management System | Printed {{ now()->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</div>
</div>
<table>
<tr><th>Ticket No</th><td>{{ $repair->ticket_no }}</td></tr>
<tr><th>Customer</th><td>{{ $repair->user->name }} ({{ $repair->contact_phone }})</td></tr>
<tr><th>Device</th><td>{{ $repair->device_type }} {{ $repair->brand }} {{ $repair->model }} / SN: {{ $repair->serial_number ?: '-' }}</td></tr>
<tr><th>Title</th><td>{{ $repair->title }}</td></tr>
<tr><th>Problem</th><td>{{ $repair->problem_description }}</td></tr>
<tr><th>Status / Urgency</th><td>{{ $repair->status }} / {{ $repair->urgency }} (Reported {{ $repair->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }})</td></tr>
@if($repair->admin_note)<tr><th>Technician Note</th><td>{{ $repair->admin_note }}</td></tr>@endif
<tr><th>Track URL</th><td>{{ route('repairs.show', $repair) }}</td></tr>
</table>
<table class="sign"><tr><td>.........................<br>Receiver</td><td>.........................<br>Customer</td></tr></table>
</body>
</html>
