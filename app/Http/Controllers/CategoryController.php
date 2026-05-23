<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $items = $category->masterClasses()
            ->with('master')
            ->withCount('bookings')
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('time_slot')
            ->get();

        $userBookings = Auth::check()
            ? Auth::user()->bookings()->pluck('master_class_id')->all()
            : [];

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('categories.show', compact('category', 'items', 'userBookings', 'categories'));
    }
}
