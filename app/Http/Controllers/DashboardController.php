<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->repairRequests();
        $history = ['histories' => fn ($query) => $query->limit(1)];

        return view('dashboard', [
            'counts' => (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'activeRepairs' => (clone $query)->whereIn('status', ['pending', 'repairing'])
                ->with($history)->latest('updated_at')->latest('id')->limit(4)->get(),
            'repairs' => (clone $query)->whereIn('status', ['completed', 'cancelled'])
                ->with($history)->latest('updated_at')->latest('id')->limit(4)->get(),
            'admin' => false,
        ]);
    }

    public function counts(Request $request)
    {
        $counts = $request->user()->repairRequests()
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return response()->json([
            'all' => (int) $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'repairing' => (int) ($counts['repairing'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ]);
    }
}
