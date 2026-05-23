<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Редактирование пользователя</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel max-w-4xl grid gap-5 md:grid-cols-2">
        @csrf
        @method('PATCH')
        <div>
            <label class="text-sm font-medium text-stone-700">Короткое имя</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Полное имя</label>
            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="field" required>
        </div>
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
                        {{ $group->name }} — {{ $group->supervisor->full_name ?: $group->supervisor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2 flex gap-3">
            <button class="btn-primary">Сохранить</button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Назад</a>
        </div>
    </form>
</x-app-layout>
