<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ $topic->title }}</h1>
                <p class="mt-1 text-sm text-stone-500">Карточка темы и история назначений.</p>
            </div>
            @can('update', $topic)
                <a href="{{ route('topics.edit', $topic) }}" class="btn-secondary">Редактировать</a>
            @endcan
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <section class="space-y-6">
            <div class="panel">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">
                        {{ $topic->is_approved ? 'Согласована' : 'На согласовании' }}
                    </span>
                    <span class="badge {{ $topic->kind === \App\Enums\TopicKind::StudentProposal ? 'badge-warning' : 'badge-neutral' }}">
                        {{ $topic->kind === \App\Enums\TopicKind::StudentProposal ? 'Предложение студента' : 'Тема каталога' }}
                    </span>
                    @if ($topic->reservedFor)
                        <span class="badge badge-neutral">Резерв: {{ $topic->reservedFor->display_name }}</span>
                    @endif
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Автор</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $topic->proposedBy->display_name }}</dd>
                    </div>
                    <div class="rounded-3xl bg-stone-50 p-4">
                        <dt class="muted">Согласовал</dt>
                        <dd class="mt-2 font-medium text-stone-900">{{ $topic->approvedBy?->display_name ?: 'Пока никто' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-3xl border border-stone-200 p-5">
                    <h2 class="section-title">Описание</h2>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $topic->description ?: 'Описание не заполнено.' }}</p>
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">История назначений</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($topic->theses as $thesis)
                        <a href="{{ route('thesis.show', $thesis) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $thesis->student->display_name }}</span>
                                <div class="flex gap-2">
                                    <span class="badge badge-neutral">{{ $thesis->assignment_type->label() }}</span>
                                    <span class="badge {{ in_array($thesis->assignment_status->value, \App\Enums\ThesisAssignmentStatus::activeValues(), true) ? 'badge-success' : ($thesis->assignment_status === \App\Enums\ThesisAssignmentStatus::Pending ? 'badge-warning' : 'badge-danger') }}">
                                        {{ $thesis->assignment_status->label() }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">Назначений по теме пока не было.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            @if (auth()->user()->isStudent() && $topic->is_approved)
                <div class="panel">
                    <h2 class="section-title">Выбрать тему</h2>
                    <p class="mt-3 text-sm text-stone-600">Если тема свободна, она сразу станет вашей активной работой.</p>
                    <form method="POST" action="{{ route('topics.apply', $topic) }}" class="mt-5">
                        @csrf
                        <button class="btn-primary">Закрепить тему</button>
                    </form>
                </div>
            @endif

            @if ((auth()->user()->isSupervisor() || auth()->user()->isAdmin()) && $topic->is_approved)
                <div class="panel">
                    <h2 class="section-title">Предложить студенту</h2>
                    <form method="POST" action="{{ route('topics.assign', $topic) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-stone-700">Студент</label>
                            <select name="student_id" class="field" @disabled($students->isEmpty()) @required($students->isNotEmpty())>
                                <option value="">Выберите студента</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->display_name }}@if ($student->studyGroup) ({{ $student->studyGroup->name }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @if ($students->isEmpty())
                                <p class="mt-2 text-sm text-stone-500">Нет студентов без выбранной темы в доступных группах.</p>
                            @endif
                        </div>
                        <button class="btn-primary" @disabled($students->isEmpty())>Отправить предложение</button>
                    </form>
                </div>
            @endif

            @can('approve', App\Models\Topic::class)
                @if (! $topic->is_approved)
                    <div class="panel">
                        <h2 class="section-title">Согласование</h2>
                        <form method="POST" action="{{ route('topics.approve', $topic) }}" class="mt-5">
                            @csrf
                            <button class="btn-primary">Согласовать тему</button>
                        </form>
                    </div>
                @endif
            @endcan
        </aside>
    </div>
</x-app-layout>
