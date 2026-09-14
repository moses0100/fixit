<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('repairs.index', ['repairs' => $request->user()->repairRequests()->filter($request)->paginate(10)->withQueryString(), 'admin' => false]);
    }

    public function create()
    {
        return view('repairs.form', ['repair' => new RepairRequest]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'device_type' => ['required', Rule::in(RepairRequest::DEVICES)],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'problem_description' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', Rule::in(array_keys(RepairRequest::URGENCIES))],
            'contact_phone' => ['required', 'string', 'regex:/^[0-9+() .-]{8,30}$/'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ], [
            'required' => 'กรุณากรอก :attribute',
            'max' => ':attribute มีขนาดหรือความยาวเกินที่กำหนด',
            'image.image' => 'กรุณาแนบไฟล์รูปภาพ',
            'image.mimes' => 'รองรับเฉพาะ JPG, PNG และ WebP',
            'contact_phone.regex' => 'กรุณากรอกเบอร์ติดต่อ 8–30 ตัวอักษร โดยใช้ตัวเลขและเครื่องหมายโทรศัพท์',
        ], ['brand' => 'ยี่ห้อ', 'title' => 'หัวข้อปัญหา', 'problem_description' => 'รายละเอียดอาการ', 'contact_phone' => 'เบอร์ติดต่อ', 'image' => 'รูปภาพ']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $path = $request->file('image')?->store('repairs', 'local');
        try {
            $repair = DB::transaction(function () use ($request, $data, $path) {
                $repair = new RepairRequest($data);
                $repair->user_id = $request->user()->id;
                // เลขชั่วคราวไม่ซ้ำกัน ก่อนเปลี่ยนเป็นเลขอ้างอิงตาม ID
                $repair->ticket_no = (string) Str::uuid();
                $repair->status = 'pending';
                $repair->image = $path;
                $repair->save();
                $repair->ticket_no = 'REP-'.now()->year.'-'.str_pad((string) $repair->id, 5, '0', STR_PAD_LEFT);
                $repair->save();

                return $repair;
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }

        return redirect()->route('repairs.show', $repair)->with('success', 'ส่งแจ้งซ่อมเรียบร้อย เลขที่ '.$repair->ticket_no);
    }

    private function authorizeOwner(Request $request, RepairRequest $repair): void
    {
        abort_unless($repair->user_id === $request->user()->id, 403);
    }

    public function show(Request $request, RepairRequest $repair)
    {
        $this->authorizeOwner($request, $repair);

        return view('repairs.show', ['repair' => $repair->load('user'), 'admin' => false]);
    }

    public function edit(Request $request, RepairRequest $repair)
    {
        $this->authorizeOwner($request, $repair);
        abort_unless($repair->status === 'pending', 403, 'แก้ไขได้เฉพาะรายการที่รอตรวจสอบ');

        return view('repairs.form', compact('repair'));
    }

    public function update(Request $request, RepairRequest $repair)
    {
        $this->authorizeOwner($request, $repair);
        $data = $this->validated($request);
        $path = $request->file('image')?->store('repairs', 'local');
        $oldPath = null;
        try {
            DB::transaction(function () use ($repair, $request, $data, $path, &$oldPath) {
                $locked = RepairRequest::whereKey($repair->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->status === 'pending', 403, 'แก้ไขได้เฉพาะรายการที่รอตรวจสอบ');
                $locked->fill($data);
                if ($path || $request->boolean('remove_image')) {
                    $oldPath = $locked->image;
                    $locked->image = $path;
                }
                $locked->save();
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('repairs.show', $repair)->with('success', 'บันทึกการแก้ไขแล้ว');
    }

    public function cancel(Request $request, RepairRequest $repair)
    {
        $this->authorizeOwner($request, $repair);
        DB::transaction(function () use ($repair) {
            $locked = RepairRequest::whereKey($repair->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'pending', 403, 'ยกเลิกได้เฉพาะรายการที่รอตรวจสอบ');
            $locked->status = 'cancelled';
            $locked->save();
        });

        return redirect()->route('repairs.show', $repair)->with('success', 'ยกเลิกรายการแล้ว โดยยังเก็บข้อมูลไว้');
    }

    public function image(Request $request, RepairRequest $repair)
    {
        abort_unless($request->user()->role === 'admin' || $repair->user_id === $request->user()->id, 403);
        abort_unless($repair->image && Storage::disk('local')->exists($repair->image), 404);

        return response()->file(Storage::disk('local')->path($repair->image), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
