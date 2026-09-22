<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Notifications\RepairStatusChanged;
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
        $repairs = RepairRequest::with('user')->filter($request)->paginate(10)->withQueryString();
        if ($request->ajax()) {
            return response()->json([
                'html' => view('repairs.table', ['repairs' => $repairs, 'admin' => true])->render()
                    .($repairs->hasPages() ? '<div class="p-4">'.(string) $repairs->links() .'</div>' : ''),
                'total' => $repairs->total(),
            ]);
        }

        return view('repairs.index', ['repairs' => $repairs, 'admin' => true]);
    }

    public function export(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RepairsExport($request),
            'repairs-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function show(RepairRequest $repair)
    {
        $repair->load(['user', 'device', 'histories.user']);
        $deviceHistory = $repair->device?->repairRequests()->whereKeyNot($repair->id)->limit(5)->get() ?? collect();

        return view('repairs.show', compact('repair', 'deviceHistory') + ['admin' => true]);
    }

    public function updateStatus(Request $request, RepairRequest $repair)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(RepairRequest::STATUSES))], 'admin_note' => ['nullable', 'string', 'max:5000']]);
        $previousStatus = $repair->status;
        $statusChanged = false;
        $noteChanged = false;
        $actorId = $request->user()->id;
        $updatedRepair = DB::transaction(function () use ($repair, $data, $actorId, &$statusChanged, &$noteChanged, $previousStatus) {
            $locked = RepairRequest::whereKey($repair->id)->lockForUpdate()->firstOrFail();
            if ($data['status'] !== $locked->status && ! in_array($data['status'], RepairRequest::TRANSITIONS[$locked->status], true)) {
                throw ValidationException::withMessages(['status' => 'ไม่สามารถเปลี่ยนสถานะตามลำดับนี้ได้ กรุณารีเฟรชเพื่อตรวจสถานะล่าสุด']);
            }
            $statusChanged = $locked->status !== $data['status'];
            $noteChanged = $locked->admin_note !== ($data['admin_note'] ?? null);
            if ($data['status'] === 'completed' && $locked->status !== 'completed') {
                $locked->completed_at = now();
            }
            $locked->status = $data['status'];
            $locked->admin_note = $data['admin_note'] ?? null;
            $locked->save();

            if ($statusChanged || $noteChanged) {
                $locked->histories()->create([
                    'user_id' => $actorId,
                    'status_from' => $previousStatus,
                    'status_to' => $locked->status,
                    'note' => $locked->admin_note ?: 'อัปเดตสถานะงาน',
                ]);
            }

            return $locked;
        });

        if ($statusChanged || $noteChanged) {
            $updatedRepair->load('user');
            $updatedRepair->user->notify(new RepairStatusChanged(
                $updatedRepair,
                $previousStatus,
                $updatedRepair->status,
                $updatedRepair->admin_note,
            ));
        }

        return redirect()->route('admin.repairs.show', $repair)->with('success', 'บันทึกสถานะและหมายเหตุแล้ว');
    }
}
