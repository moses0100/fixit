<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->repairRequests();

        return view('dashboard', [
            'counts' => (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'repairs' => $query->latest('created_at')->latest('id')->limit(5)->get(),
            'admin' => false,
        ]);
    }
}
