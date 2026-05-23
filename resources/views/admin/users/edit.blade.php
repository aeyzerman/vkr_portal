<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Редактирование пользователя</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel max-w-4xl space-y-5">
        @csrf
        @method('PATCH')

        <x-user-name-fields :user="$user" />

        <div>
            <label class="text-sm font-medium text-stone-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Группа</label>
            <select name="study_group_id" class="field">
                <option value="">Без группы</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}" @selected((int) old('study_group_id', $user->study_group_id) === $group->id)>
                        {{ $group->name }} — {{ $group->supervisor->display_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3">
            <button class="btn-primary">Сохранить</button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Назад</a>
        </div>
    </form>
</x-app-layout>
