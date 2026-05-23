<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Новая группа</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.groups.store') }}" class="panel max-w-4xl grid gap-5 md:grid-cols-2">
        @csrf
        <div>
            <label class="text-sm font-medium text-stone-700">Название</label>
            <input type="text" name="name" value="{{ old('name') }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Курс</label>
            <input type="number" name="course" value="{{ old('course') }}" min="1" max="6" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Код специальности</label>
            <input type="text" name="specialty_code" value="{{ old('specialty_code') }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Название специальности</label>
            <input type="text" name="specialty_name" value="{{ old('specialty_name') }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Куратор</label>
            <select name="supervisor_id" class="field" required>
                @foreach ($supervisors as $supervisor)
                    <option value="{{ $supervisor->id }}">{{ $supervisor->full_name ?: $supervisor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Год набора</label>
            <input type="number" name="enrollment_year" value="{{ old('enrollment_year') }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Дедлайн выбора темы</label>
            <input type="date" name="topic_selection_deadline" value="{{ old('topic_selection_deadline') }}" class="field">
        </div>
        <div class="md:col-span-2 flex gap-3">
            <button class="btn-primary">Создать</button>
            <a href="{{ route('admin.groups.index') }}" class="btn-secondary">Отмена</a>
        </div>
    </form>
</x-app-layout>
