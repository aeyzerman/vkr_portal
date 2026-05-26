<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ $thesis->topic?->title ?? 'Карточка работы' }}</h1>
                <p class="mt-1 text-sm text-stone-500">Полная информация о назначении темы и ходе выполнения.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
        <section class="space-y-6">
            <div class="panel">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge badge-neutral">{{ $thesis->assignment_type->label() }}</span>
                    <span class="badge {{ in_array($thesis->assignment_status->value, \App\Enums\ThesisAssignmentStatus::activeValues(), true) ? 'badge-success' : ($thesis->assignment_status === \App\Enums\ThesisAssignmentStatus::Pending ? 'badge-warning' : 'badge-danger') }}">
                        {{ $thesis->assignment_status->label() }}
                    </span>
                    <span class="badge badge-neutral">{{ $thesis->status->label() }}</span>
                </div>

                <dl class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Студент</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $thesis->student->display_name }}</dd>
                    </div>
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Руководитель</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $thesis->supervisor->display_name }}</dd>
                    </div>
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Группа</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $thesis->studyGroup->name }}</dd>
                    </div>
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Оценка</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $thesis->grade ?: 'Нет' }}</dd>
                    </div>
                </dl>

                @php
                    $canManageDocuments = auth()->id() === $thesis->student_id && $thesis->isActive();
                @endphp
                @if ($thesis->document_path || $canManageDocuments)
                    @include('thesis.partials.document-panel', [
                        'thesis' => $thesis,
                        'canManage' => $canManageDocuments,
                    ])
                @endif
            </div>
        </section>

        <aside class="space-y-6">
            @can('updateStatus', $thesis)
                <div class="panel">
                    <h2 class="section-title">Обновить статус</h2>
                    <form method="POST" action="{{ route('thesis.status.update', $thesis) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="text-sm font-medium text-stone-700">Статус</label>
                            <select name="status" class="field">
                                @foreach (\App\Enums\ThesisStatus::cases() as $status)
                                    @if ($status !== \App\Enums\ThesisStatus::None)
                                        <option value="{{ $status->value }}" @selected($thesis->status === $status)>{{ $status->label() }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-stone-700">Оценка</label>
                            <input type="number" min="2" max="5" name="grade" value="{{ old('grade', $thesis->grade) }}" class="field">
                        </div>
                        <button class="btn-primary">Сохранить</button>
                    </form>
                </div>
            @endcan

            <div class="panel">
                <h2 class="section-title">Таймлайн</h2>
                <div class="mt-5 space-y-3 text-sm text-stone-600">
                    <p>Предложена или назначена: {{ optional($thesis->assigned_at)->format('d.m.Y H:i') ?: 'Нет' }}</p>
                    <p>Ответ студента: {{ optional($thesis->assignment_responded_at)->format('d.m.Y H:i') ?: 'Нет' }}</p>
                    <p>Начата: {{ optional($thesis->started_at)->format('d.m.Y H:i') ?: 'Нет' }}</p>
                    <p>Сдана: {{ optional($thesis->submitted_at)->format('d.m.Y H:i') ?: 'Нет' }}</p>
                    <p>Завершена: {{ optional($thesis->done_at)->format('d.m.Y H:i') ?: 'Нет' }}</p>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
