<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Профиль</h2>
        <p class="mt-1 text-sm text-gray-600">Фамилия, имя, отчество и email.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <x-user-name-fields :user="$user" />

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="field mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Сохранить</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-gray-600">Сохранено.</p>
            @endif
        </div>
    </form>
</section>
