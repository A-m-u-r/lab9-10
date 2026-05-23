<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return Auth::check()
            ? redirect()->route('home')
            : view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\p{L}\s\-']+$/u"],
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'regex:/[A-Za-zА-Яа-яЁё]/u', 'regex:/\d/'],
            'role' => ['required', Rule::in(['visitor', 'master'])],
        ], $this->messages(), $this->attributes());

        $phone = $this->normalizePhone($validated['phone']);
        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'Номер телефона должен быть в формате +7 XXX XXX-XX-XX.',
            ]);
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'photo' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Регистрация прошла успешно. Добро пожаловать!');
    }

    public function showLogin()
    {
        return Auth::check()
            ? redirect()->route('home')
            : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], $this->messages(), $this->attributes());

        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Вы успешно вошли.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7'.substr($digits, 1);
        }

        return strlen($digits) === 11 && $digits[0] === '7'
            ? '+'.$digits
            : null;
    }

    private function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'email' => 'Поле «:attribute» содержит некорректный адрес.',
            'unique' => 'Пользователь с таким email уже зарегистрирован.',
            'min' => 'Поле «:attribute» должно содержать минимум :min символов.',
            'max' => 'Поле «:attribute» должно содержать не более :max символов.',
            'confirmed' => 'Пароли не совпадают.',
            'regex' => 'Поле «:attribute» заполнено неверно.',
            'in' => 'Поле «:attribute» содержит недопустимое значение.',
        ];
    }

    private function attributes(): array
    {
        return [
            'full_name' => 'ФИО',
            'email' => 'Email',
            'phone' => 'Номер телефона',
            'password' => 'Пароль',
            'role' => 'Роль',
        ];
    }
}
