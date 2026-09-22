<?php

namespace App\Exports;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RepairsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private ?Request $request = null) {}

    public function query()
    {
        $query = RepairRequest::with('user');
        if ($this->request) $query->filter($this->request);
        return $query->latest('created_at')->latest('id');
    }

    public function headings(): array
    {
        return ['เลขแจ้งซ่อม', 'ผู้แจ้ง', 'อีเมล', 'เบอร์ติดต่อ', 'อุปกรณ์', 'ยี่ห้อ', 'รุ่น', 'Serial', 'หัวข้อ', 'ความเร่งด่วน', 'สถานะ', 'วันที่แจ้ง', 'วันที่เสร็จ'];
    }

    public function map($repair): array
    {
        return [
            $repair->ticket_no,
            $repair->user?->name,
            $repair->user?->email,
            $repair->contact_phone,
            $repair->device_type,
            $repair->brand,
            $repair->model,
            $repair->serial_number,
            $repair->title,
            RepairRequest::URGENCIES[$repair->urgency] ?? $repair->urgency,
            RepairRequest::STATUSES[$repair->status] ?? $repair->status,
            $repair->created_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i'),
            $repair->completed_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i'),
        ];
    }
}
