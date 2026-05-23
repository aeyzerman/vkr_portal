<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Моя ВКР</h1>
            <p class="mt-1 text-sm text-stone-500">Текущий прогресс, предложения темы и история завершённых работ.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($thesis)
            <section class="panel">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="section-title">{{ $thesis->topic?->title ?? 'Без темы' }}</h2>
                        <p class="mt-1 text-sm text-stone-500">{{ $thesis->studyGroup->name }} • Руководитель: {{ $thesis->supervisor->display_name }}</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="badge badge-success">{{ $thesis->assignment_status->label() }}</span>
                        <span class="badge badge-neutral">{{ $thesis->status->label() }}</span>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-3xl bg-stone-50 p-5">
                        <p class="muted">Начало работы</p>
                        <p class="mt-2 text-sm text-stone-700">{{ optional($thesis->started_at)->format('d.m.Y H:i') ?: 'Ещё не начата' }}</p>
                    </div>
                    <div class="rounded-3xl bg-stone-50 p-5">
                        <p class="muted">Последняя сдача</p>
                        <p class="mt-2 text-sm text-stone-700">{{ optional($thesis->submitted_at)->format('d.m.Y H:i') ?: 'Ещё не сдавалась' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ route('thesis.show', $thesis) }}" class="btn-secondary">Открыть карточку</a>
                    @if ($thesis->document_path)
                        <a href="{{ route('thesis.document.download', $thesis) }}" class="btn-secondary">Скачать текущий файл</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('thesis.document.upload', $thesis) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium text-stone-700">Загрузить файл работы</label>
                        <input type="file" name="document" class="field" required>
                    </div>
                    <button class="btn-primary">Загрузить</button>
                </form>
            </section>
        @else
            <section class="panel-muted">
                <p class="text-sm text-stone-600">Активной ВКР пока нет. Выберите тему на странице каталога.</p>
            </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="panel">
                <h2 class="section-title">Предложения преподавателя</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($pendingOffers as $offer)
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                            <h3 class="font-semibold text-stone-900">{{ $offer->topic?->title ?? 'Без темы' }}</h3>
                            <p class="mt-2 text-sm text-stone-600">Руководитель: {{ $offer->supervisor->display_name }}</p>
                            <div class="mt-4 flex gap-3">
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
                        <p class="text-sm text-stone-500">Необработанных предложений нет.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">История работ</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($history as $item)
                        <a href="{{ route('thesis.show', $item) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $item->topic?->title ?? 'Без темы' }}</span>
                                <span class="badge badge-neutral">{{ $item->status->label() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-stone-500">{{ $item->studyGroup->name }} • {{ optional($item->done_at)->format('d.m.Y') }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">История завершённых работ пуста.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
