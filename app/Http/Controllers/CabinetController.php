<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class CabinetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user?->isMaster(), 403);

        $items = $user->masterClasses()
            ->with('category')
            ->withCount('bookings')
            ->orderBy('date')
            ->orderBy('time_slot')
            ->get();

        return view('cabinet.index', compact('items'));
    }
}
