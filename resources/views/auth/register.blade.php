@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post" action="{{ route('register.store') }}" class="form" novalidate id="form-register">
            @csrf
            <h2>Форма регистрации</h2>

            <div class="form-group">
                <label for="full_name">ФИО</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                       required minlength="2" maxlength="100"
                       pattern="[\p{L}\s\-']+"
                       data-rule="fullname"
                       autocomplete="name">
                <small class="hint">От 2 до 100 символов, только буквы, пробелы, дефисы.</small>
                @error('full_name')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       required maxlength="254"
                       data-rule="email"
                       autocomplete="email">
                @error('email')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="phone">Номер телефона</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       required maxlength="20"
                       placeholder="+7 999 123-45-67"
                       data-rule="phone"
                       autocomplete="tel">
                <small class="hint">Российский номер: +7 / 8 и 10 цифр.</small>
                @error('phone')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       required minlength="8" maxlength="72"
                       data-rule="password"
                       autocomplete="new-password">
                <small class="hint">Минимум 8 символов, буквы и цифры.</small>
                @error('password')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Повторите пароль</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required minlength="8" maxlength="72"
                       data-rule="match" data-match="password"
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label>Я регистрируюсь как</label>
                <label class="radio"><input type="radio" name="role" value="visitor" @checked(old('role', 'visitor') === 'visitor')> Посетитель</label>
                <label class="radio"><input type="radio" name="role" value="master" @checked(old('role') === 'master')> Ведущий мастер-класса</label>
                @error('role')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Зарегистрироваться</button>
                <a href="{{ route('login') }}" class="form__alt">У меня уже есть аккаунт</a>
            </div>
        </form>
    </div>
</div>
@endsection
