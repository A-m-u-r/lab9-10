<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterClassController extends Controller
{
    public function create()
    {
        $this->requireMaster();

        return view('master_classes.form', [
            'mode' => 'create',
            'masterClass' => null,
            'categories' => Category::orderBy('name')->get(),
            'allowedSlots' => MasterClass::ALLOWED_SLOTS,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->requireMaster();
        $validated = $request->validate($this->storeRules(), $this->messages(), $this->attributes());

        $this->ensureSlotIsFree($user, $validated['date'], $validated['time_slot']);

        try {
            MasterClass::create($validated + ['master_id' => $user->id]);
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'time_slot' => 'Это время уже занято, выберите другое.',
            ]);
        }

        return redirect()->route('cabinet')->with('success', 'Мастер-класс создан.');
    }

    public function show(MasterClass $masterClass)
    {
        $user = $this->requireMaster();
        $this->authorizeOwned($masterClass, $user);

        $masterClass->load(['category', 'participants'])->loadCount('bookings');

        return view('master_classes.show', compact('masterClass'));
    }

    public function edit(MasterClass $masterClass)
    {
        $user = $this->requireMaster();
        $this->authorizeOwned($masterClass, $user);

        return view('master_classes.form', [
            'mode' => 'edit',
            'masterClass' => $masterClass->load('category'),
            'categories' => Category::orderBy('name')->get(),
            'allowedSlots' => MasterClass::ALLOWED_SLOTS,
        ]);
    }

    public function update(Request $request, MasterClass $masterClass)
    {
        $user = $this->requireMaster();
        $this->authorizeOwned($masterClass, $user);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ], $this->messages(), $this->attributes());

        $masterClass->update($validated);

        return redirect()->route('cabinet')->with('success', 'Изменения сохранены.');
    }

    private function requireMaster(): User
    {
        $user = Auth::user();
        abort_unless($user?->isMaster(), 403);

        return $user;
    }

    private function authorizeOwned(MasterClass $masterClass, User $user): void
    {
        abort_unless((int) $masterClass->master_id === (int) $user->id, 403);
    }

    private function ensureSlotIsFree(User $user, string $date, string $slot): void
    {
        $taken = MasterClass::query()
            ->where('master_id', $user->id)
            ->whereDate('date', $date)
            ->where('time_slot', $slot)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'time_slot' => 'На эту дату и время у вас уже запланирован мастер-класс.',
            ]);
        }
    }

    private function storeRules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_slot' => ['required', Rule::in(MasterClass::ALLOWED_SLOTS)],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'exists' => 'Выбранный вариант не найден.',
            'date_format' => 'Поле «:attribute» должно быть в формате ГГГГ-ММ-ДД.',
            'after_or_equal' => 'Дата не может быть в прошлом.',
            'in' => 'Поле «:attribute» содержит недопустимое значение.',
            'integer' => 'Поле «:attribute» должно быть целым числом.',
            'numeric' => 'Поле «:attribute» должно быть числом.',
            'min' => 'Поле «:attribute» меньше допустимого значения.',
            'max' => 'Поле «:attribute» больше допустимого значения.',
        ];
    }

    private function attributes(): array
    {
        return [
            'category_id' => 'Вид творчества',
            'title' => 'Название',
            'description' => 'Описание',
            'date' => 'Дата',
            'time_slot' => 'Время',
            'capacity' => 'Количество человек',
            'price' => 'Стоимость',
        ];
    }
}
