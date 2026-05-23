<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ $user->full_name ?: $user->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary">Редактировать</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <aside class="space-y-6">
            <div class="panel">
                <h2 class="section-title">Роли</h2>
                <form method="POST" action="{{ route('admin.users.permissions', $user) }}" class="mt-5 space-y-3">
                    @csrf
                    @php
                        $roles = [
                            App\Models\User::PERM_STUDENT => 'Студент',
                            App\Models\User::PERM_SUPERVISOR => 'Преподаватель',
                            App\Models\User::PERM_REVIEWER => 'Рецензент',
                            App\Models\User::PERM_COMMISSION => 'Комиссия',
                            App\Models\User::PERM_ADMIN => 'Администратор',
                        ];
                    @endphp
                    @foreach ($roles as $bit => $label)
                        <label class="flex items-center gap-3 rounded-2xl border border-stone-200 px-4 py-3 text-sm text-stone-700">
                            <input type="checkbox" name="permissions[]" value="{{ $bit }}" @checked(($user->permissions & $bit) !== 0)>
                            {{ $label }}
                        </label>
                    @endforeach
                    <button class="btn-primary">Сохранить роли</button>
                </form>
            </div>

            <div class="panel">
                <h2 class="section-title">Группа</h2>
                <p class="mt-3 text-sm text-stone-600">{{ $user->studyGroup?->name ?: 'Не назначена' }}</p>
                @if ($user->studyGroup?->supervisor)
                    <p class="mt-1 text-sm text-stone-500">Куратор: {{ $user->studyGroup->supervisor->full_name ?: $user->studyGroup->supervisor->name }}</p>
                @endif
            </div>
        </aside>

        <section class="space-y-6">
            <div class="panel">
                <h2 class="section-title">Работы пользователя</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($user->theses as $thesis)
                        <a href="{{ route('thesis.show', $thesis) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $thesis->topic?->title ?? 'Без темы' }}</span>
                                <span class="badge badge-neutral">{{ $thesis->status->label() }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">Работ нет.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">Предложенные темы</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($user->proposedTopics as $topic)
                        <a href="{{ route('topics.show', $topic) }}" class="block rounded-3xl border border-stone-200 p-4 transition hover:border-stone-300">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-stone-900">{{ $topic->title }}</span>
                                <span class="badge {{ $topic->is_approved ? 'badge-success' : 'badge-warning' }}">{{ $topic->is_approved ? 'Согласована' : 'На согласовании' }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-stone-500">Тем пока нет.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
