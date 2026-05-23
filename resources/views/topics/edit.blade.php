<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Редактирование темы</h1>
            <p class="mt-1 text-sm text-stone-500">Пока тема не закреплена за студентом, её можно уточнить.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('topics.update', $topic) }}" class="panel max-w-3xl space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label class="text-sm font-medium text-stone-700">Название темы</label>
            <input type="text" name="title" value="{{ old('title', $topic->title) }}" class="field" required>
        </div>

        <div>
            <label class="text-sm font-medium text-stone-700">Описание</label>
            <textarea name="description" rows="6" class="field">{{ old('description', $topic->description) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button class="btn-primary">Обновить</button>
            <a href="{{ route('topics.show', $topic) }}" class="btn-secondary">Назад</a>
        </div>
    </form>
</x-app-layout>
