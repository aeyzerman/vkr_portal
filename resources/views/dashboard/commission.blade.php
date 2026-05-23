<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Комиссия и рецензенты</h1>
            <p class="mt-1 text-sm text-stone-500">Работы, готовые к рецензированию или уже одобренные.</p>
        </div>
    </x-slot>

    <div class="panel">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Тема</th>
                        <th>Руководитель</th>
                        <th>Группа</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($theses as $thesis)
                        <tr>
                            <td><a href="{{ route('thesis.show', $thesis) }}" class="font-medium text-stone-900 hover:text-stone-700">{{ $thesis->student->display_name }}</a></td>
                            <td>{{ $thesis->topic?->title ?? 'Без темы' }}</td>
                            <td>{{ $thesis->supervisor->display_name }}</td>
                            <td>{{ $thesis->studyGroup->name }}</td>
                            <td><span class="badge badge-warning">{{ $thesis->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-stone-500">Подходящих работ нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
