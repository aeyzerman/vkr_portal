<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-stone-900">Вход</h1>
        <p class="mt-2 text-sm text-stone-500">
            Нет аккаунта?
            <a href="{{ route('register') }}" class="font-medium text-stone-800 underline decoration-stone-300 underline-offset-2 transition hover:text-stone-950">
                Зарегистрироваться
            </a>
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="field mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Пароль" />
            <x-text-input id="password" class="field mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500" name="remember">
                <span class="ms-2 text-sm text-stone-600">Запомнить меня</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm text-stone-600 underline decoration-stone-300 underline-offset-2 transition hover:text-stone-900" href="{{ route('password.request') }}">
                    Забыли пароль?
                </a>
            @endif

            <button type="submit" class="btn-primary">
                Войти
            </button>
        </div>
    </form>
</x-guest-layout>
