<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Пользователи</h1>
            <p class="mt-1 text-sm text-stone-500">Роли, группы и поиск по участникам портала.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="panel grid gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-medium text-stone-700">Поиск</label>
                <input type="text" name="search" value="{{ request('search') }}" class="field" placeholder="Имя или email">
            </div>
            <div>
                <label class="text-sm font-medium text-stone-700">Роль</label>
                <select name="role" class="field">
                    <option value="">Все</option>
                    @foreach ($roles as $bit => $label)
                        <option value="{{ $bit }}" @selected((string) request('role') === (string) $bit)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button class="btn-primary">Фильтровать</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Сбросить</a>
            </div>
        </form>

        <div class="panel">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Группа</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($users as $user)
                            <tr>
                                <td><a href="{{ route('admin.users.show', $user) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $user->full_name ?: $user->name }}</a></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->studyGroup?->name ?: 'Не назначена' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
