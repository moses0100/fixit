<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('dashboard', [
            'counts' => RepairRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'repairs' => RepairRequest::with('user')->latest('created_at')->latest('id')->limit(5)->get(),
            'admin' => true,
        ]);
    }

    public function index(Request $request)
    {
        return view('repairs.index', ['repairs' => RepairRequest::with('user')->filter($request)->paginate(10)->withQueryString(), 'admin' => true]);
    }

    public function show(RepairRequest $repair)
    {
        return view('repairs.show', ['repair' => $repair->load('user'), 'admin' => true]);
    }

    public function updateStatus(Request $request, RepairRequest $repair)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(RepairRequest::STATUSES))], 'admin_note' => ['nullable', 'string', 'max:5000']]);
        DB::transaction(function () use ($repair, $data) {
            $locked = RepairRequest::whereKey($repair->id)->lockForUpdate()->firstOrFail();
            if ($data['status'] !== $locked->status && ! in_array($data['status'], RepairRequest::TRANSITIONS[$locked->status], true)) {
                throw ValidationException::withMessages(['status' => 'ไม่สามารถเปลี่ยนสถานะตามลำดับนี้ได้ กรุณารีเฟรชเพื่อตรวจสถานะล่าสุด']);
            }
            if ($data['status'] === 'completed' && $locked->status !== 'completed') {
                $locked->completed_at = now();
            }
            $locked->status = $data['status'];
            $locked->admin_note = $data['admin_note'] ?? null;
            $locked->save();
        });

        return redirect()->route('admin.repairs.show', $repair)->with('success', 'บันทึกสถานะและหมายเหตุแล้ว');
    }
}
