@props([
    'studyGroup',
    'canManage' => false,
])

@php
    $searchUrl = route('groups.students.search', $studyGroup);
@endphp

<div class="panel" data-group-membership data-search-url="{{ $searchUrl }}" data-store-url="{{ route('groups.members.store', $studyGroup) }}">
    <h2 class="section-title">Состав группы</h2>

    @if ($canManage)
        <div class="mt-5 rounded-3xl border border-stone-200 bg-stone-50 p-4">
            <p class="text-sm font-medium text-stone-800">Добавить студента</p>
            <p class="mt-1 text-xs text-stone-500">Поиск по фамилии, имени или email. Показываются только студенты без группы.</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <input type="search" class="field min-w-[16rem] flex-1" placeholder="Например, Иванов" data-student-search-input>
                <button type="button" class="btn-secondary" data-student-search-button>Найти</button>
            </div>
            <div class="mt-3 space-y-2" data-student-search-results hidden></div>
        </div>
    @endif

    <div class="mt-5 space-y-3">
        @forelse ($studyGroup->students as $student)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-stone-200 p-4">
                <div>
                    <p class="font-medium text-stone-900">{{ $student->display_name }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $student->email }}</p>
                </div>
                @if ($canManage)
                    <form method="POST" action="{{ route('groups.members.destroy', [$studyGroup, $student]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger text-sm">Исключить</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-stone-500">В группе пока нет студентов.</p>
        @endforelse
    </div>

    @if ($canManage && $studyGroup->pendingJoinRequests->isNotEmpty())
        <div class="mt-8 border-t border-stone-200 pt-6">
            <h3 class="text-sm font-semibold text-stone-800">Заявки на вступление</h3>
            <div class="mt-4 space-y-3">
                @foreach ($studyGroup->pendingJoinRequests as $joinRequest)
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4">
                        <p class="font-medium text-stone-900">{{ $joinRequest->user->display_name }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $joinRequest->user->email }}</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('join-requests.approve', $joinRequest) }}">
                                @csrf
                                <button class="btn-primary text-sm">Одобрить</button>
                            </form>
                            <form method="POST" action="{{ route('join-requests.reject', $joinRequest) }}">
                                @csrf
                                <button class="btn-secondary text-sm">Отклонить</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        @vite('resources/js/group-membership.js')
    @endpush
@endonce
