<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Админ-панель</h1>
            <p class="mt-1 text-sm text-stone-500">Сводка по группам, темам и активным назначениям.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="stat-card"><p class="muted">Активные работы</p><p class="mt-3 text-3xl font-semibold">{{ $stats['total_theses'] }}</p></div>
            <div class="stat-card"><p class="muted">Ожидают ответа</p><p class="mt-3 text-3xl font-semibold">{{ $stats['pending_offers'] }}</p></div>
            <div class="stat-card"><p class="muted">Все темы</p><p class="mt-3 text-3xl font-semibold">{{ $stats['total_topics'] }}</p></div>
            <div class="stat-card"><p class="muted">На согласовании</p><p class="mt-3 text-3xl font-semibold">{{ $stats['pending_topics'] }}</p></div>
            <div class="stat-card"><p class="muted">Группы</p><p class="mt-3 text-3xl font-semibold">{{ $stats['total_groups'] }}</p></div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="panel">
                <div class="flex items-center justify-between">
                    <h2 class="section-title">Последние группы</h2>
                    <a href="{{ route('admin.groups.index') }}" class="btn-secondary">Все группы</a>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($recentGroups as $group)
                        <a href="{{ route('admin.groups.show', $group) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-stone-900">{{ $group->name }}</p>
                                    <p class="mt-1 text-sm text-stone-500">{{ $group->supervisor->display_name }}</p>
                                </div>
                                <span class="badge badge-neutral">{{ optional($group->topic_selection_deadline)->format('d.m.Y') ?: 'без дедлайна' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between">
                    <h2 class="section-title">Последние темы</h2>
                    <a href="{{ route('topics.index') }}" class="btn-secondary">Все темы</a>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($recentTopics as $topic)
                        <a href="{{ route('topics.show', $topic) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-stone-900">{{ $topic->title }}</p>
                                    <p class="mt-1 text-sm text-stone-500">{{ $topic->proposedBy->display_name }}</p>
                                </div>
                                <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">
                                    {{ $topic->is_approved ? 'Согласована' : 'На согласовании' }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
