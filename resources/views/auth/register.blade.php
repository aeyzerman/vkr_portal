<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-stone-900">Регистрация</h1>
        <p class="mt-2 text-sm text-stone-500">
            Уже есть аккаунт?
            <a href="{{ route('login') }}" class="font-medium text-stone-800 underline decoration-stone-300 underline-offset-2 transition hover:text-stone-950">
                Войти
            </a>
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-user-name-fields />

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="field mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Пароль" />
            <x-text-input id="password" class="field mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Подтверждение пароля" />
            <x-text-input id="password_confirmation" class="field mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        @if ($adminRegistrationEnabled ?? false)
            <div>
                <x-input-label for="admin_token" value="Токен администратора" />
                <x-text-input id="admin_token" class="field mt-1 block w-full" type="password" name="admin_token" autocomplete="off" />
                <p class="mt-2 text-xs text-stone-500">Первый администратор: укажите значение из ADMIN_REGISTRATION_TOKEN в .env</p>
                <x-input-error :messages="$errors->get('admin_token')" class="mt-2" />
            </div>
        @endif

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary">
                Зарегистрироваться
            </button>
        </div>
    </form>
</x-guest-layout>
