<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RepairRequest extends Model
{
    use HasFactory;

    public const STATUSES = ['pending' => 'รอตรวจสอบ', 'repairing' => 'กำลังซ่อม', 'completed' => 'ซ่อมเสร็จแล้ว', 'cancelled' => 'ยกเลิก'];

    public const URGENCIES = ['low' => 'ต่ำ', 'medium' => 'ปานกลาง', 'high' => 'สูง'];

    public const DEVICES = ['Notebook', 'Desktop PC', 'All-in-One', 'Monitor', 'Other'];

    public const TRANSITIONS = ['pending' => ['repairing', 'cancelled'], 'repairing' => ['completed'], 'completed' => [], 'cancelled' => []];

    // รับเฉพาะข้อมูลฟอร์มผู้ใช้ สิทธิ์/สถานะ/เจ้าของกำหนดจาก Controller เท่านั้น
    protected $fillable = ['device_type', 'brand', 'model', 'serial_number', 'title', 'problem_description', 'urgency', 'contact_phone'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', Rule::in(array_keys(self::STATUSES))],
            'urgency' => ['nullable', 'string', Rule::in(array_keys(self::URGENCIES))],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim()->toString().'%';
            $query->where(function (Builder $query) use ($term) {
                $query->where('ticket_no', 'like', $term)->orWhere('title', 'like', $term)
                    ->orWhere('serial_number', 'like', $term)->orWhere('brand', 'like', $term)
                    ->orWhere('model', 'like', $term)->orWhere('device_type', 'like', $term)
                    ->orWhere('contact_phone', 'like', $term)
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }
        if (array_key_exists((string) $request->input('status'), self::STATUSES)) {
            $query->where('status', $request->input('status'));
        }
        if (array_key_exists((string) $request->input('urgency'), self::URGENCIES)) {
            $query->where('urgency', $request->input('urgency'));
        }

        $direction = $request->input('sort') === 'oldest' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }
}
