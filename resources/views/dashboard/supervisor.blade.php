<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Кабинет преподавателя</h1>
                <p class="mt-1 text-sm text-stone-500">Группы, предложенные темы и активные работы студентов.</p>
            </div>
            <a href="{{ route('topics.create') }}" class="btn-primary">Добавить тему</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <div class="stat-card">
                <p class="muted">Группы</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $groups->count() }}</p>
            </div>
            <div class="stat-card">
                <p class="muted">Ожидают ответа</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $pendingOffers->count() }}</p>
            </div>
            <div class="stat-card">
                <p class="muted">Активные работы</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $theses->count() }}</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <div class="panel">
                <h2 class="section-title">Закреплённые группы</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($groups as $group)
                        <a href="{{ route('groups.show', $group) }}" class="block rounded-3xl border border-stone-200 p-5 transition hover:border-stone-300">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-stone-900">{{ $group->name }}</h3>
                                    <p class="mt-1 text-sm text-stone-500">{{ $group->specialty_code }} • {{ $group->specialty_name }}</p>
                                </div>
                                <span class="badge badge-neutral">{{ $group->students_count }} студентов</span>
                            </div>
                            <p class="mt-4 text-sm text-stone-600">Дедлайн выбора темы: {{ optional($group->topic_selection_deadline)->format('d.m.Y') ?: 'не задан' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">Ожидают ответа студента</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($pendingOffers as $offer)
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                            <h3 class="font-semibold text-stone-900">{{ $offer->topic?->title ?? 'Без темы' }}</h3>
                            <p class="mt-2 text-sm text-stone-600">{{ $offer->student->display_name }} • {{ $offer->studyGroup->name }}</p>
                            <p class="mt-1 text-sm text-stone-500">Предложено {{ optional($offer->assigned_at)->format('d.m.Y H:i') }}</p>
                        </div>
                    @empty
                        <div class="panel-muted">
                            <p class="text-sm text-stone-600">Все предложения уже обработаны.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="panel">
                <h2 class="section-title">Активные работы</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Студент</th>
                                <th>Тема</th>
                                <th>Группа</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($theses as $thesis)
                                <tr>
                                    <td><a href="{{ route('thesis.show', $thesis) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $thesis->student->display_name }}</a></td>
                                    <td>{{ $thesis->topic?->title ?? 'Без темы' }}</td>
                                    <td>{{ $thesis->studyGroup->name }}</td>
                                    <td><span class="badge badge-neutral">{{ $thesis->status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-stone-500">Активных работ пока нет.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">Мои темы</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($myTopics as $topic)
                        <a href="{{ route('topics.show', $topic) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $topic->title }}</span>
                                <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">
                                    {{ $topic->is_approved ? 'Согласована' : 'Черновик' }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-stone-500">Назначений: {{ $topic->theses_count }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">Вы ещё не добавляли темы.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
