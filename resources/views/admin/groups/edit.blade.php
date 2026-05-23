<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Редактирование группы {{ $studyGroup->name }}</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.groups.update', $studyGroup) }}" class="panel max-w-4xl grid gap-5 md:grid-cols-2">
        @csrf
        @method('PATCH')
        <div>
            <label class="text-sm font-medium text-stone-700">Название</label>
            <input type="text" name="name" value="{{ old('name', $studyGroup->name) }}" class="field" required>
        </div>
        <div>
            <x-study-group-course-select :value="$studyGroup->course" />
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Код специальности</label>
            <input type="text" name="specialty_code" value="{{ old('specialty_code', $studyGroup->specialty_code) }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Название специальности</label>
            <input type="text" name="specialty_name" value="{{ old('specialty_name', $studyGroup->specialty_name) }}" class="field" required>
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Куратор</label>
            <select name="supervisor_id" class="field" required>
                @foreach ($supervisors as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected((int) old('supervisor_id', $studyGroup->supervisor_id) === $supervisor->id)>{{ $supervisor->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-study-group-year-select :value="$studyGroup->enrollment_year" />
        </div>
        <div>
            <label class="text-sm font-medium text-stone-700">Дедлайн выбора темы</label>
            <input type="date" name="topic_selection_deadline" value="{{ old('topic_selection_deadline', optional($studyGroup->topic_selection_deadline)->format('Y-m-d')) }}" class="field">
        </div>
        <div class="md:col-span-2 flex gap-3">
            <button class="btn-primary">Сохранить</button>
            <a href="{{ route('admin.groups.show', $studyGroup) }}" class="btn-secondary">Назад</a>
        </div>
    </form>
</x-app-layout>
