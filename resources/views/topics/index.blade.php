<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Темы ВКР</h1>
                <p class="mt-1 text-sm text-stone-500">Каталог тем и предложения студентов.</p>
            </div>
            <a href="{{ route('topics.create') }}" class="btn-primary">Новая тема</a>
        </div>
    </x-slot>

    <div class="panel">
        <div class="space-y-4">
            @forelse ($topics as $topic)
                <div class="rounded-3xl border border-stone-200 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('topics.show', $topic) }}" class="text-lg font-semibold text-stone-900 hover:text-stone-700">{{ $topic->title }}</a>
                                <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">
                                    {{ $topic->is_approved ? 'Согласована' : 'На согласовании' }}
                                </span>
                                <span class="badge {{ $topic->kind === \App\Enums\TopicKind::StudentProposal ? 'badge-warning' : 'badge-neutral' }}">
                                    {{ $topic->kind->label() }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($topic->description, 220) }}</p>
                        </div>
                        <div class="text-right text-sm text-stone-500">
                            <p>Автор: {{ $topic->proposedBy->full_name ?: $topic->proposedBy->name }}</p>
                            @if ($topic->reservedFor)
                                <p class="mt-1">Резерв: {{ $topic->reservedFor->full_name ?: $topic->reservedFor->name }}</p>
                            @endif
                            <p class="mt-1">Активных назначений: {{ $topic->active_assignments_count }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="panel-muted">
                    <p class="text-sm text-stone-600">Темы пока не добавлены.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $topics->links() }}
        </div>
    </div>
</x-app-layout>
