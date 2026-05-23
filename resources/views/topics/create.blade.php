<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Новая тема</h1>
            <p class="mt-1 text-sm text-stone-500">Студент отправляет тему на согласование, преподаватель пополняет каталог.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('topics.store') }}" class="panel max-w-3xl space-y-5">
        @csrf

        <div>
            <label class="text-sm font-medium text-stone-700">Название темы</label>
            <input type="text" name="title" value="{{ old('title') }}" class="field" required>
        </div>

        <div>
            <label class="text-sm font-medium text-stone-700">Описание</label>
            <textarea name="description" rows="6" class="field">{{ old('description') }}</textarea>
        </div>

        @if ($user->isSupervisor())
            <div>
                <label class="text-sm font-medium text-stone-700">Сразу предложить студенту</label>
                <select name="reserved_for" class="field">
                    <option value="">Без резервирования</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(old('reserved_for') == $student->id)>
                            {{ $student->full_name ?: $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex gap-3">
            <button class="btn-primary">Сохранить</button>
            <a href="{{ route('topics.index') }}" class="btn-secondary">Отмена</a>
        </div>
    </form>
</x-app-layout>
