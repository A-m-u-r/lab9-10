@extends('layouts.app')

@php
    $isEdit = $mode === 'edit';
    $today = now()->toDateString();
@endphp

@section('title', $isEdit ? 'Редактирование мастер-класса' : 'Новый мастер-класс')

@section('content')
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post"
              action="{{ $isEdit ? route('master-classes.update', $masterClass) : route('master-classes.store') }}"
              class="form" novalidate id="form-mc"
              data-edit="{{ $isEdit ? '1' : '0' }}"
              data-slots-url="{{ route('api.slots') }}"
              @if($isEdit) data-mc-id="{{ $masterClass->id }}" @endif>
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <h2>{{ $isEdit ? 'Редактирование мастер-класса' : 'Форма добавления мастер-класса' }}</h2>

            <div class="form-group">
                <label for="category_id">Вид творчества</label>
                <select id="category_id" name="category_id" required @disabled($isEdit)>
                    <option value="">— выберите —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $masterClass?->category_id) === (int) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="title">Название мастер-класса</label>
                <input type="text" id="title" name="title"
                       value="{{ old('title', $masterClass?->title) }}"
                       required minlength="3" maxlength="120"
                       @readonly($isEdit)>
                @error('title')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="description">Описание мастер-класса</label>
                <textarea id="description" name="description" required minlength="10" maxlength="2000"
                          data-rule="length">{{ old('description', $masterClass?->description) }}</textarea>
                <small class="hint">От 10 до 2000 символов.</small>
                @error('description')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="date">Дата</label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', $masterClass?->date?->toDateString()) }}"
                       min="{{ $today }}" required @readonly($isEdit)>
                @error('date')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="time_slot">Время</label>
                <select id="time_slot" name="time_slot" required @disabled($isEdit)>
                    <option value="">— выберите дату —</option>
                    @foreach($allowedSlots as $slot)
                        <option value="{{ $slot }}" @selected(old('time_slot', $masterClass?->time_slot) === $slot)>
                            {{ \App\Models\MasterClass::slotLabel($slot) }}
                        </option>
                    @endforeach
                </select>
                <small class="hint">Сетка: 9-11, 11-13, 13-15, 15-17. Занятые слоты будут отключены автоматически.</small>
                @error('time_slot')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="capacity">Количество человек в группе</label>
                <input type="number" id="capacity" name="capacity"
                       value="{{ old('capacity', $masterClass?->capacity) }}"
                       required min="1" max="100" step="1"
                       @readonly($isEdit)>
                @error('capacity')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="price">Стоимость (₽)</label>
                <input type="number" id="price" name="price"
                       value="{{ old('price', $masterClass?->price) }}"
                       required min="0" max="1000000" step="0.01">
                @error('price')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn">{{ $isEdit ? 'Сохранить' : 'Создать мастер-класс' }}</button>
                <a href="{{ route('cabinet') }}" class="form__alt">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
