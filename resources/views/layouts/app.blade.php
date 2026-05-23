<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="@yield('body_class')">
<div class="header">
    <div class="row grid middle between">
        <div class="logo">
            <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo.png') }}" alt="logo"></a>
        </div>
        <div class="title">Клуб любителей творчества «ОчУмелые ручки»</div>
        <div class="auth">
            @auth
                <span class="auth__name">
                    {{ auth()->user()->full_name }}
                    <span class="auth__role">({{ auth()->user()->isMaster() ? 'ведущий' : 'посетитель' }})</span>
                </span>
                @if(auth()->user()->isMaster())
                    <a href="{{ route('cabinet') }}">Кабинет</a>
                @endif
                <form action="{{ route('logout') }}" method="post" class="auth__logout">
                    @csrf
                    <button type="submit" class="auth__link-btn">Выход</button>
                </form>
            @else
                <a href="{{ route('login') }}">Вход</a>
                <span class="auth__sep">/</span>
                <a href="{{ route('register') }}">Регистрация</a>
            @endauth
        </div>
    </div>
</div>
<div class="row row--nogutter">
    <div class="menu-burger">
        <div class="burger"><div></div><div></div><div></div></div>
    </div>
</div>

@if(session('success'))
    <div class="row"><div class="row--small flash flash--ok">{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="row"><div class="row--small flash flash--err">{{ session('error') }}</div></div>
@endif

<div class="main">
    @yield('content')
</div>
<div class="row row--nogutter"><div class="line"></div></div>
<div class="footer">
    <div class="row">
        <div class="row--small grid between">
            <div class="address">Наш адрес: ВДНХ, 120В</div>
            <div class="tel">Тел: 8&nbsp;912&nbsp;345&nbsp;67-65</div>
            <div class="copy">© Copyright, 2026</div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/app.js') }}" defer></script>
</body>
</html>
