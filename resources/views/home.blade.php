@extends('layouts.app')

@section('title', 'ОчУмелые ручки')

@section('content')
<div class="row">
    <div class="hover"></div>
    <div class="title">ОчУмелые ручки</div>
    <div class="row--small grid between">
        <div class="content">
            <h2>Добро пожаловать!</h2>
            <p>Мы — клуб любителей ручного творчества. Здесь мастера проводят увлекательные занятия по архитектурному моделированию, кулинарии, резьбе по дереву и многому другому.</p>
            <p>Выберите интересующий вид творчества в меню справа, посмотрите расписание и запишитесь на ближайший мастер-класс. Если вы ведущий, войдите в систему, чтобы добавить собственное расписание.</p>

            @auth
                @if($myBookings->isNotEmpty())
                    <h2 class="mt">Мои записи</h2>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Вид творчества</th>
                            <th>Мастер-класс</th>
                            <th>Ведущий</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($myBookings as $booking)
                            @php($mc = $booking->masterClass)
                            <tr>
                                <td>{{ $mc->date->format('d.m.Y') }}</td>
                                <td>{{ \App\Models\MasterClass::slotLabel($mc->time_slot) }}</td>
                                <td>{{ $mc->category->name }}</td>
                                <td>{{ $mc->title }}</td>
                                <td>{{ $mc->master->full_name }}</td>
                                <td>
                                    <form action="{{ route('bookings.cancel', $booking) }}" method="post" class="inline-form" onsubmit="return confirm('Отменить запись?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--small">Отменить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="muted mt">Вы пока не записаны ни на один мастер-класс.</p>
                @endif
            @endauth
        </div>
        <ul class="menu">
            @foreach($categories as $category)
                <li><a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
