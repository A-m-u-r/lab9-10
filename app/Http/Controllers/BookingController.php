<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MasterClass;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class BookingController extends Controller
{
    public function confirm(MasterClass $masterClass)
    {
        $user = Auth::user();
        $masterClass->load(['category', 'master'])->loadCount('bookings');

        if ($user->isMaster() && (int) $masterClass->master_id === (int) $user->id) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Вы не можете записаться на собственный мастер-класс.');
        }

        if ($this->alreadyBooked($masterClass->id)) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Вы уже записаны на этот мастер-класс.');
        }

        if ($masterClass->availablePlaces() < 1) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Свободных мест больше нет.');
        }

        return view('bookings.confirm', compact('masterClass'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'master_class_id' => ['required', 'integer', 'exists:master_classes,id'],
            'action' => ['required', 'in:confirm,cancel'],
        ]);

        $masterClass = MasterClass::with('category')->findOrFail($validated['master_class_id']);

        if ($validated['action'] === 'cancel') {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('success', 'Запись не выполнена.');
        }

        $user = Auth::user();
        if ($user->isMaster() && (int) $masterClass->master_id === (int) $user->id) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Вы не можете записаться на собственный мастер-класс.');
        }

        try {
            DB::transaction(function () use ($masterClass, $user): void {
                $fresh = MasterClass::query()
                    ->withCount('bookings')
                    ->lockForUpdate()
                    ->findOrFail($masterClass->id);

                if ($fresh->bookings()->count() >= $fresh->capacity) {
                    throw new RuntimeException('no_places');
                }

                Booking::create([
                    'user_id' => $user->id,
                    'master_class_id' => $fresh->id,
                ]);
            });
        } catch (QueryException) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Вы уже записаны на этот мастер-класс.');
        } catch (RuntimeException) {
            return redirect()->route('categories.show', $masterClass->category_id)
                ->with('error', 'Свободных мест больше нет.');
        }

        return redirect()->route('categories.show', $masterClass->category_id)
            ->with('success', 'Запись подтверждена.');
    }

    public function cancel(Booking $booking)
    {
        abort_unless((int) $booking->user_id === (int) Auth::id(), 403);

        $booking->delete();

        return redirect()->route('home')->with('success', 'Запись отменена.');
    }

    public function slots(Request $request)
    {
        $user = Auth::user();
        if (! $user?->isMaster()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:Y-m-d'],
            'exclude' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_date'], 422);
        }

        $validated = $validator->validated();

        $taken = MasterClass::query()
            ->where('master_id', $user->id)
            ->whereDate('date', $validated['date'])
            ->when($validated['exclude'] ?? null, fn ($query, $exclude) => $query->whereKeyNot($exclude))
            ->pluck('time_slot')
            ->values();

        return response()->json([
            'taken' => $taken,
            'allowed' => MasterClass::ALLOWED_SLOTS,
        ]);
    }

    private function alreadyBooked(int $masterClassId): bool
    {
        return Booking::query()
            ->where('user_id', Auth::id())
            ->where('master_class_id', $masterClassId)
            ->exists();
    }
}
