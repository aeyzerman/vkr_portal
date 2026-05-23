<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Кабинет студента</h1>
                <p class="mt-1 text-sm text-stone-500">Темы, предложения преподавателя и текущая ВКР в одном месте.</p>
            </div>
            <a href="{{ route('topics.create') }}" class="btn-primary">Предложить свою тему</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="space-y-6">
            <div class="panel">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="section-title">Текущая работа</h2>
                        <p class="muted">Активная ВКР после принятия или назначения темы.</p>
                    </div>
                    <a href="{{ route('thesis.my') }}" class="btn-secondary">Открыть кабинет работы</a>
                </div>

                @if ($thesis)
                    <div class="mt-6 rounded-3xl bg-stone-50 p-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="badge badge-success">{{ $thesis->assignment_status->label() }}</span>
                            <span class="badge badge-neutral">{{ $thesis->status->label() }}</span>
                            <span class="badge badge-neutral">{{ $thesis->studyGroup->name }}</span>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-stone-900">{{ $thesis->topic?->title ?? 'Тема не указана' }}</h3>
                        <p class="mt-2 text-sm text-stone-600">Руководитель: {{ $thesis->supervisor->display_name }}</p>
                        <p class="mt-1 text-sm text-stone-500">Назначена: {{ optional($thesis->assigned_at)->format('d.m.Y H:i') }}</p>
                    </div>
                @else
                    <div class="mt-6 panel-muted">
                        <p class="text-sm text-stone-600">Активной работы пока нет. Можно выбрать тему из каталога или отправить свою тему на согласование.</p>
                    </div>
                @endif
            </div>

            <div class="panel">
                <h2 class="section-title">Доступные темы</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($availableTopics as $topic)
                        <div class="rounded-3xl border border-stone-200 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-stone-900">{{ $topic->title }}</h3>
                                    <p class="mt-2 text-sm text-stone-500">{{ \Illuminate\Support\Str::limit($topic->description, 180) }}</p>
                                </div>
                                <span class="badge {{ $topic->kind === \App\Enums\TopicKind::StudentProposal ? 'badge-warning' : 'badge-neutral' }}">
                                    {{ $topic->kind === \App\Enums\TopicKind::StudentProposal ? 'Авторская тема' : 'Каталог' }}
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-stone-500">
                                <span>Предложил: {{ $topic->proposedBy->display_name }}</span>
                                @if ($topic->reserved_for)
                                    <span class="badge badge-warning">Зарезервирована за вами</span>
                                @endif
                            </div>
                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('topics.show', $topic) }}" class="btn-secondary">Подробнее</a>
                                @if (! $thesis)
                                    <form method="POST" action="{{ route('topics.apply', $topic) }}">
                                        @csrf
                                        <button class="btn-primary">Выбрать тему</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="panel-muted">
                            <p class="text-sm text-stone-600">Подходящих свободных тем пока нет.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            @include('dashboard.partials.student-group', ['user' => auth()->user()])

            <div class="panel">
                <h2 class="section-title">Предложения преподавателя</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($pendingOffers as $offer)
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                            <h3 class="font-semibold text-stone-900">{{ $offer->topic?->title ?? 'Тема без названия' }}</h3>
                            <p class="mt-2 text-sm text-stone-600">{{ $offer->supervisor->display_name }}</p>
                            <p class="mt-1 text-sm text-stone-500">Предложено {{ optional($offer->assigned_at)->format('d.m.Y H:i') }}</p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('thesis.accept', $offer) }}">
                                    @csrf
                                    <button class="btn-primary">Принять</button>
                                </form>
                                <form method="POST" action="{{ route('thesis.decline', $offer) }}">
                                    @csrf
                                    <button class="btn-danger">Отклонить</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="panel-muted">
                            <p class="text-sm text-stone-600">Новых предложений нет.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">Мои предложенные темы</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($myTopics as $topic)
                        <a href="{{ route('topics.show', $topic) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $topic->title }}</span>
                                <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">
                                    {{ $topic->is_approved ? 'Согласована' : 'На согласовании' }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">Вы ещё не предлагали темы.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
