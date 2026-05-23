@extends('layouts.app')

@section('title', 'Личный кабинет')
@section('body_class', 'dp')

@section('content')
<div class="row">
    <div class="hover"></div>
    <div class="title"></div>
    <div class="row--small grid between">
        <div class="content driver-page">
            <div class="driver-page-photo">
                <img src="{{ asset('assets/photos/' . (auth()->user()->photo ?: 'master-1.jpg')) }}" alt="{{ auth()->user()->full_name }}">
            </div>
            <div class="driver-page-name">{{ auth()->user()->full_name }}</div>
            <div class="driver-page-text">
                <div class="driver-page-my">Мои мастер-классы</div>
                @if($items->isEmpty())
                    <p class="muted">Вы ещё не создали ни одного мастер-класса.</p>
                @else
                    <table class="driver-page-table">
                        <tbody>
                        @foreach($items as $mc)
                            <tr>
                                <td>
                                    {{ $mc->date->format('d.m.Y') }}<br>
                                    {{ \App\Models\MasterClass::slotLabel($mc->time_slot) }}
                                </td>
                                <td>
                                    <b>{{ $mc->title }}</b><br>
                                    <span class="muted">{{ $mc->category->name }}</span><br>
                                    Записано: {{ $mc->bookings_count }} / {{ $mc->capacity }},
                                    стоимость: {{ number_format((float) $mc->price, 2, ',', ' ') }} ₽
                                    <div class="actions">
                                        <a href="{{ route('master-classes.show', $mc) }}" class="btn btn--small">Участники</a>
                                        <a href="{{ route('master-classes.edit', $mc) }}" class="btn btn--small">Редактировать</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="driver-page-btn-wrapper">
                <a href="{{ route('master-classes.create') }}" class="driver-page-btn btn">Добавить мастер-класс</a>
            </div>
        </div>
        <ul class="menu">
            @foreach(\App\Models\Category::orderBy('name')->get(['id', 'name']) as $category)
                <li><a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
