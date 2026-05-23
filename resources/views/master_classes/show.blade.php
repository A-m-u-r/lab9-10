@extends('layouts.app')

@section('title', 'Мастер-класс: ' . $masterClass->title)

@section('content')
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <h2>{{ $masterClass->title }}</h2>
        <p class="muted">
            {{ $masterClass->category->name }} · {{ $masterClass->date->format('d.m.Y') }} · {{ \App\Models\MasterClass::slotLabel($masterClass->time_slot) }}
        </p>
        <p>{!! nl2br(e($masterClass->description)) !!}</p>
        <p>
            Стоимость: <b>{{ number_format((float) $masterClass->price, 2, ',', ' ') }} ₽</b>
            · Записано: <b>{{ $masterClass->bookings_count }} / {{ $masterClass->capacity }}</b>
        </p>

        <h2 class="mt">Участники</h2>
        @if($masterClass->participants->isEmpty())
            <p class="muted">Пока никто не записался.</p>
        @else
            <table class="table">
                <thead>
                <tr><th>#</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Записан</th></tr>
                </thead>
                <tbody>
                @foreach($masterClass->participants as $participant)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $participant->full_name }}</td>
                        <td>{{ $participant->email }}</td>
                        <td>{{ $participant->phone }}</td>
                        <td>{{ $participant->pivot->created_at?->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <p class="mt"><a href="{{ route('cabinet') }}" class="btn btn--small">Назад в кабинет</a></p>
    </div>
</div>
@endsection
