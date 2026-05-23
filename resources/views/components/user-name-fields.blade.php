@props(['user' => null])

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="text-sm font-medium text-stone-700">Фамилия</label>
        <input type="text" name="last_name" value="{{ old('last_name', $user?->last_name) }}" class="field" required autocomplete="family-name">
        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>
    <div>
        <label class="text-sm font-medium text-stone-700">Имя</label>
        <input type="text" name="first_name" value="{{ old('first_name', $user?->first_name) }}" class="field" required autocomplete="given-name">
        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>
    <div>
        <label class="text-sm font-medium text-stone-700">Отчество</label>
        <input type="text" name="patronymic" value="{{ old('patronymic', $user?->patronymic) }}" class="field" autocomplete="additional-name">
        <x-input-error :messages="$errors->get('patronymic')" class="mt-2" />
    </div>
</div>
