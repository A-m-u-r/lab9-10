@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="row">
    <div class="hover"></div>
    <div class="title">{{ $category->name }}</div>
    <div class="row--small grid between">
        <div class="content">
            <img src="{{ asset('assets/photos/' . $category->image) }}" alt="{{ $category->name }}" class="content__img">
            <p>{!! nl2br(e($category->description)) !!}</p>
        </div>
        <ul class="menu">
            @foreach($categories as $item)
                <li><a href="{{ route('categories.show', $item) }}">{{ $item->name }}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="row shedule">
        <div class="row--small">
            <h2>Расписание</h2>
            @if($items->isEmpty())
                <p>Пока нет запланированных мастер-классов.</p>
            @endif
            <div class="drivers">
                @foreach($items as $mc)
                    @php
                        $free = $mc->availablePlaces();
                        $alreadyBooked = in_array($mc->id, $userBookings, true);
                        $isOwn = auth()->check() && auth()->user()->isMaster() && (int) auth()->id() === (int) $mc->master_id;
                    @endphp
                    <div class="driver grid">
                        <div class="driver-left grid">
                            <div class="driver-photo">
                                <img src="{{ asset('assets/photos/' . ($mc->master->photo ?: 'master-1.jpg')) }}" alt="{{ $mc->master->full_name }}">
                            </div>
                            <div class="driver-text">
                                <div class="driver-name">{{ $mc->master->full_name }}</div>
                                <div class="driver-name driver-name--mc">{{ $mc->title }}</div>
                                <div class="driver-desc">{!! nl2br(e($mc->description)) !!}</div>
                                <div class="driver-meta">
                                    <span>Стоимость: <b>{{ number_format((float) $mc->price, 2, ',', ' ') }} ₽</b></span>
                                    <span>Свободных мест: <b>{{ $free }} / {{ $mc->capacity }}</b></span>
                                </div>
                            </div>
                        </div>
                        <div class="driver-right">
                            @guest
                                <a href="{{ route('login') }}" class="driver-btn">Войдите для записи</a>
                            @else
                                @if($isOwn)
                                    <span class="driver-tag">Ваш мастер-класс</span>
                                @elseif($alreadyBooked)
                                    <span class="driver-tag">Вы записаны</span>
                                @elseif($free <= 0)
                                    <span class="driver-tag driver-tag--full">Мест нет</span>
                                @else
                                    <a href="{{ route('bookings.confirm', $mc) }}" class="driver-btn">записаться</a>
                                @endif
                            @endguest
                            <div class="driver-time">{{ $mc->date->format('d.m.Y') }}<br>{{ \App\Models\MasterClass::slotLabel($mc->time_slot) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
