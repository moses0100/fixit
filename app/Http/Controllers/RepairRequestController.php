<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\Device;
use App\Models\User;
use App\Notifications\NewRepairRequestReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $repairs = $request->user()->repairRequests()->filter($request)->paginate(10)->withQueryString();
        if ($request->ajax()) {
            return response()->json([
                'html' => view('repairs.table', ['repairs' => $repairs, 'admin' => false])->render()
                    .($repairs->hasPages() ? '<div class="p-4">'.(string) $repairs->links() .'</div>' : ''),
                'total' => $repairs->total(),
            ]);
        }

        return view('repairs.index', ['repairs' => $repairs, 'admin' => false]);
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
                $repair->device_id = Device::forRepair($request->user(), $data)->id;
                // เลขชั่วคราวไม่ซ้ำกัน ก่อนเปลี่ยนเป็นเลขอ้างอิงตาม ID
                $repair->ticket_no = (string) Str::uuid();
                $repair->status = 'pending';
                $repair->image = $path;
                $repair->save();
                $repair->ticket_no = 'REP-'.now()->year.'-'.str_pad((string) $repair->id, 5, '0', STR_PAD_LEFT);
                $repair->save();
                $repair->histories()->create([
                    'user_id' => $request->user()->id,
                    'status_to' => 'pending',
                    'note' => 'สร้างรายการแจ้งซ่อม',
                ]);

                return $repair;
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }

        User::where('role', 'admin')->get()->each(fn (User $admin) => $admin->notify(new NewRepairRequestReceived($repair)));

        return redirect()->route('repairs.show', $repair)->with('success', 'ส่งแจ้งซ่อมเรียบร้อย เลขที่ '.$repair->ticket_no);
    }

    private function authorizeOwner(Request $request, RepairRequest $repair): void
    {
        abort_unless($repair->user_id === $request->user()->id, 403);
    }

    public function show(Request $request, RepairRequest $repair)
    {
        $this->authorizeOwner($request, $repair);
        $repair->load(['user', 'device', 'histories.user']);
        $deviceHistory = $repair->device?->repairRequests()->whereKeyNot($repair->id)->limit(5)->get() ?? collect();

        return view('repairs.show', compact('repair', 'deviceHistory') + ['admin' => false]);
    }

    public function suggest(Request $request)
    {
        $empty = ['count' => 0, 'hint' => null];
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) return response()->json($empty);
        $words = array_values(array_filter(preg_split('/\s+/u', $q), fn ($w) => mb_strlen($w) >= 2));
        $words = array_slice($words, 0, 5);
        if (! $words) return response()->json($empty);
        $base = RepairRequest::query()
            ->where('status', 'completed')
            ->where('is_guidance', true)
            ->whereNotNull('admin_note');
        $base->where(function ($inner) use ($words) {
            foreach ($words as $w) {
                $inner->orWhere('title', 'like', "%{$w}%")->orWhere('problem_description', 'like', "%{$w}%");
            }
        });
        $total = (clone $base)->count();
        if (! $total) return response()->json($empty);
        $top = (clone $base)->select('admin_note', DB::raw('COUNT(*) as matches'))->groupBy('admin_note')->orderByDesc('matches')->first();
        return response()->json([
            'count' => $total,
            'hint' => $top ? mb_substr((string) $top->admin_note, 0, 140) : null,
        ]);
    }

    public function slip(Request $request, RepairRequest $repair)
    {
        abort_unless($request->user()->role === 'admin' || $repair->user_id === $request->user()->id, 403);
        $repair->load(['user', 'device', 'histories.user']);

        return view('repairs.slip', compact('repair'));
    }

    public function slipPdf(Request $request, RepairRequest $repair)
    {
        abort_unless($request->user()->role === 'admin' || $repair->user_id === $request->user()->id, 403);
        $repair->load(['user', 'device']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('repairs.slip-pdf', compact('repair'))
            ->setPaper('a4', 'portrait')
            ->download($repair->ticket_no.'.pdf');
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
                $locked->device_id = Device::forRepair($request->user(), $data)->id;
                $locked->fill($data);
                if ($path || $request->boolean('remove_image')) {
                    $oldPath = $locked->image;
                    $locked->image = $path;
                }
                $locked->save();
                $locked->histories()->create([
                    'user_id' => $request->user()->id,
                    'status_from' => $locked->status,
                    'status_to' => $locked->status,
                    'note' => 'ผู้แจ้งแก้ไขรายละเอียดงาน',
                ]);
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
        DB::transaction(function () use ($request, $repair) {
            $locked = RepairRequest::whereKey($repair->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'pending', 403, 'ยกเลิกได้เฉพาะรายการที่รอตรวจสอบ');
            $locked->status = 'cancelled';
            $locked->save();
            $locked->histories()->create([
                'user_id' => $request->user()->id,
                'status_from' => 'pending',
                'status_to' => 'cancelled',
                'note' => 'ผู้แจ้งยกเลิกรายการ',
            ]);
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
