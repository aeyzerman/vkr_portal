<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Учебные группы</h1>
                <p class="mt-1 text-sm text-stone-500">Кураторы, студенты и дедлайны выбора тем.</p>
            </div>
            <a href="{{ route('admin.groups.create') }}" class="btn-primary">Новая группа</a>
        </div>
    </x-slot>

    <div class="panel">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Специальность</th>
                        <th>Куратор</th>
                        <th>Студенты</th>
                        <th>Дедлайн</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($groups as $group)
                        <tr>
                            <td><a href="{{ route('admin.groups.show', $group) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $group->name }}</a></td>
                            <td>{{ $group->specialty_code }} • {{ $group->specialty_name }}</td>
                            <td>{{ $group->supervisor->full_name ?: $group->supervisor->name }}</td>
                            <td>{{ $group->students_count }}</td>
                            <td>{{ optional($group->topic_selection_deadline)->format('d.m.Y') ?: 'Нет' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $groups->links() }}</div>
    </div>
</x-app-layout>
