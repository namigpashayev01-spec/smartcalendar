<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->user,   fn($q,$v) => $q->whereHas('user', fn($u) => $u->where('username',$v)))
            ->when($request->action, fn($q,$v) => $q->where('action',$v))
            ->when($request->search, fn($q,$v) => $q->where('target','like',"%$v%"))
            ->latest('created_at')
            ->limit(500)
            ->get();

        $users   = AuditLog::with('user')->get()->pluck('user.username')->filter()->unique()->values();
        $actions = AuditLog::distinct()->pluck('action');

        return view('log.index', compact('logs','users','actions'));
    }

    public function destroy()
    {
        if (!Auth::user()->isAdmin()) abort(403);
        AuditLog::truncate();
        AuditLog::create(['user_id'=>Auth::id(),'action'=>'Sildi','target'=>'jurnal təmizləndi','created_at'=>now()]);
        return back()->with('success', 'Jurnal təmizləndi.');
    }
}
