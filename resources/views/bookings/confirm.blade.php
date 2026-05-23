@extends('layouts.app')

@section('title', 'Подтверждение записи')

@section('content')
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <h2>Подтверждение записи</h2>
        <table class="table">
            <tbody>
            <tr><th>ФИО</th><td>{{ auth()->user()->full_name }}</td></tr>
            <tr><th>Вид творчества</th><td>{{ $masterClass->category->name }}</td></tr>
            <tr><th>Мастер-класс</th><td>{{ $masterClass->title }}</td></tr>
            <tr><th>Ведущий</th><td>{{ $masterClass->master->full_name }}</td></tr>
            <tr><th>Дата</th><td>{{ $masterClass->date->format('d.m.Y') }}</td></tr>
            <tr><th>Время</th><td>{{ \App\Models\MasterClass::slotLabel($masterClass->time_slot) }}</td></tr>
            <tr><th>Стоимость</th><td>{{ number_format((float) $masterClass->price, 2, ',', ' ') }} ₽</td></tr>
            </tbody>
        </table>

        <form method="post" action="{{ route('bookings.store') }}" class="confirm-form">
            @csrf
            <input type="hidden" name="master_class_id" value="{{ $masterClass->id }}">
            <button type="submit" name="action" value="confirm" class="btn">Подтвердить запись</button>
            <button type="submit" name="action" value="cancel" class="btn btn--ghost">Отменить</button>
        </form>
    </div>
</div>
@endsection
