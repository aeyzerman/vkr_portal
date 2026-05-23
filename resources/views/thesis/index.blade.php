<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Работы</h1>
            <p class="mt-1 text-sm text-stone-500">Канбан-доска ВКР: перетаскивайте карточки между колонками статусов.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('thesis.index') }}" class="panel flex flex-wrap items-end gap-4">
            @if ($groups->isNotEmpty())
                <div class="min-w-[12rem] flex-1">
                    <label class="text-sm font-medium text-stone-700">Группа</label>
                    <select name="group" class="field">
                        <option value="">Все группы</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" @selected((int) request('group') === $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex items-end">
                <label class="inline-flex items-center gap-3 rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-700">
                    <input type="checkbox" name="show_completed" value="1" @checked(request()->boolean('show_completed'))>
                    Показывать завершённые
                </label>
            </div>
            <div class="flex gap-3">
                <button class="btn-primary">Применить</button>
                <a href="{{ route('thesis.index') }}" class="btn-secondary">Сбросить</a>
            </div>
        </form>

        <div id="thesis-board" class="kanban-board">
            @foreach ($columns as $column)
                @php($columnTheses = $thesesByStatus[$column->value] ?? collect())
                <section class="kanban-column" data-status="{{ $column->value }}">
                    <header class="kanban-column-header">
                        <h2 class="kanban-column-title">{{ $column->label() }}</h2>
                        <span class="kanban-column-count">{{ $columnTheses->count() }}</span>
                    </header>

                    <div class="kanban-column-body" data-drop-zone>
                        @foreach ($columnTheses as $thesis)
                            @can('updateStatus', $thesis)
                                @php($draggable = true)
                            @else
                                @php($draggable = false)
                            @endcan

                            <article
                                class="kanban-card {{ $draggable ? 'kanban-card--draggable' : 'kanban-card--readonly' }}"
                                @if ($draggable) draggable="true" @endif
                                data-thesis-id="{{ $thesis->id }}"
                                data-update-url="{{ route('thesis.status.update', $thesis) }}"
                                data-status="{{ $thesis->status->value }}"
                            >
                                <a href="{{ route('thesis.show', $thesis) }}" class="kanban-card-title">
                                    {{ $thesis->topic?->title ?? 'Без темы' }}
                                </a>

                                <div class="kanban-card-assignee">
                                    <span class="kanban-avatar" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($thesis->student->display_name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="kanban-card-assignee-name">{{ $thesis->student->display_name }}</p>
                                        <p class="kanban-card-meta">{{ $thesis->studyGroup->name }}</p>
                                    </div>
                                </div>

                                <div class="kanban-card-footer">
                                    <span class="badge badge-neutral">{{ $thesis->assignment_type->label() }}</span>
                                    <span class="kanban-card-meta">{{ $thesis->supervisor->display_name }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/thesis-board.js')
    @endpush
</x-app-layout>
