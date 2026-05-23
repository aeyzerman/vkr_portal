<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Работы</h1>
            <p class="mt-1 text-sm text-stone-500">Общий список ВКР с фильтрами по стадии выполнения и назначению темы.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('thesis.index') }}" class="panel grid gap-4 md:grid-cols-4">
            <div>
                <label class="text-sm font-medium text-stone-700">Статус работы</label>
                <select name="status" class="field">
                    <option value="">Все</option>
                    @foreach ($statuses as $status)
                        @if ($status !== \App\Enums\ThesisStatus::None)
                            <option value="{{ $status->value }}" @selected((int) request('status') === $status->value)>{{ $status->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-stone-700">Статус назначения</label>
                <select name="assignment_status" class="field">
                    <option value="">Все</option>
                    @foreach ($assignmentStatuses as $status)
                        @if ($status !== \App\Enums\ThesisAssignmentStatus::None)
                            <option value="{{ $status->value }}" @selected((int) request('assignment_status') === $status->value)>{{ $status->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-3 rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-700">
                    <input type="checkbox" name="show_completed" value="1" @checked(request()->boolean('show_completed'))>
                    Показывать завершённые
                </label>
            </div>
            <div class="flex items-end gap-3">
                <button class="btn-primary">Фильтровать</button>
                <a href="{{ route('thesis.index') }}" class="btn-secondary">Сбросить</a>
            </div>
        </form>

        <div class="panel">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Студент</th>
                            <th>Тема</th>
                            <th>Назначение</th>
                            <th>Статус</th>
                            <th>Группа</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($theses as $thesis)
                            <tr>
                                <td><a href="{{ route('thesis.show', $thesis) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $thesis->student->full_name ?: $thesis->student->name }}</a></td>
                                <td>{{ $thesis->topic?->title ?? 'Без темы' }}</td>
                                <td>
                                    <div class="flex flex-col gap-2">
                                        <span class="badge badge-neutral">{{ $thesis->assignment_type->label() }}</span>
                                        <span class="badge {{ in_array($thesis->assignment_status->value, \App\Enums\ThesisAssignmentStatus::activeValues(), true) ? 'badge-success' : ($thesis->assignment_status === \App\Enums\ThesisAssignmentStatus::Pending ? 'badge-warning' : 'badge-danger') }}">
                                            {{ $thesis->assignment_status->label() }}
                                        </span>
                                    </div>
                                </td>
                                <td><span class="badge badge-neutral">{{ $thesis->status->label() }}</span></td>
                                <td>{{ $thesis->studyGroup->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-stone-500">Работ по текущему фильтру нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $theses->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
