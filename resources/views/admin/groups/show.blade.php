<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ $studyGroup->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $studyGroup->specialty_code }} • {{ $studyGroup->specialty_name }}</p>
            </div>
            <div class="flex gap-3">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.groups.edit', $studyGroup) }}" class="btn-secondary">Редактировать</a>
                @endif
                <form method="POST" action="{{ route('groups.random-assign', $studyGroup) }}">
                    @csrf
                    <button class="btn-primary">Случайно распределить темы</button>
                </form>
            </div>
        </div>
    </x-slot>

    @php
        $canManage = auth()->user()->isAdmin()
            || (auth()->user()->isSupervisor() && $studyGroup->supervisor_id === auth()->id());
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <div class="stat-card"><p class="muted">Куратор</p><p class="mt-3 text-lg font-semibold">{{ $studyGroup->supervisor->display_name }}</p></div>
            <div class="stat-card"><p class="muted">Студенты</p><p class="mt-3 text-3xl font-semibold">{{ $studyGroup->students->count() }}</p></div>
            <div class="stat-card"><p class="muted">Дедлайн выбора</p><p class="mt-3 text-lg font-semibold">{{ optional($studyGroup->topic_selection_deadline)->format('d.m.Y') ?: 'Не задан' }}</p></div>
        </section>

        <x-group-membership-panel :study-group="$studyGroup" :can-manage="$canManage" />

        <section class="panel">
            <h2 class="section-title">Свободные темы каталога</h2>
            <div class="mt-5 space-y-3">
                @forelse ($availableTopics as $topic)
                    <a href="{{ route('topics.show', $topic) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                        <p class="font-medium text-stone-900">{{ $topic->title }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ $topic->proposedBy->display_name }}</p>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">Свободных тем нет.</p>
                @endforelse
            </div>
        </section>

        <section class="panel">
            <h2 class="section-title">Все назначения и работы</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Студент</th>
                            <th>Тема</th>
                            <th>Назначение</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($theses as $thesis)
                            <tr>
                                <td><a href="{{ route('thesis.show', $thesis) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $thesis->student->display_name }}</a></td>
                                <td>{{ $thesis->topic?->title ?? 'Без темы' }}</td>
                                <td>{{ $thesis->assignment_type->label() }} / {{ $thesis->assignment_status->label() }}</td>
                                <td>{{ $thesis->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
