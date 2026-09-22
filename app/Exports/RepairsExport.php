<?php

namespace App\Exports;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class RepairsExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder
{
    public function __construct(private ?Request $request = null) {}

    // กัน formula injection: ข้อความขึ้นต้น = + - @ ให้เป็นตัวหนังสือล้วน
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && preg_match('/^[=+\-@]/u', $value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

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
