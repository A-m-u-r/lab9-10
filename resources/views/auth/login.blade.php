@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post" action="{{ route('login.store') }}" class="form" novalidate id="form-login">
            @csrf
            <h2>Вход</h2>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required maxlength="254" data-rule="email" autocomplete="username">
                @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       required maxlength="72" autocomplete="current-password">
                @error('password')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Войти</button>
                <a href="{{ route('register') }}" class="form__alt">Регистрация</a>
            </div>
        </form>
    </div>
</div>
@endsection
