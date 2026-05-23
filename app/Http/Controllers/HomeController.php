<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $myBookings = collect();

        if (Auth::check()) {
            $myBookings = Booking::query()
                ->with(['masterClass.category', 'masterClass.master'])
                ->join('master_classes', 'master_classes.id', '=', 'bookings.master_class_id')
                ->where('bookings.user_id', Auth::id())
                ->orderBy('master_classes.date')
                ->orderBy('master_classes.time_slot')
                ->select('bookings.*')
                ->get();
        }

        return view('home', compact('categories', 'myBookings'));
    }
}
