<div class="panel">
    <h2 class="section-title">Учебная группа</h2>

    @if ($user->studyGroup)
        <div class="mt-5 rounded-3xl bg-stone-50 p-5">
            <p class="font-semibold text-stone-900">{{ $user->studyGroup->name }}</p>
            <p class="mt-2 text-sm text-stone-600">
                Куратор: {{ $user->studyGroup->supervisor->display_name }}
            </p>
            <a href="{{ route('groups.show', $user->studyGroup) }}" class="btn-secondary mt-4 inline-flex">Открыть группу</a>
        </div>
    @elseif ($pendingJoinRequest)
        <div class="mt-5 rounded-3xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm text-amber-900">
                Заявка на вступление в группу
                <strong>{{ $pendingJoinRequest->studyGroup->name }}</strong>
                ожидает решения куратора.
            </p>
        </div>
    @else
        <p class="mt-3 text-sm text-stone-600">
            Выберите группу и отправьте заявку. Куратор сможет одобрить или отклонить её.
        </p>
        <div class="mt-5 space-y-3">
            @foreach ($availableGroups as $group)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-stone-200 p-4">
                    <div>
                        <p class="font-medium text-stone-900">{{ $group->name }}</p>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $group->specialty_code }} • куратор: {{ $group->supervisor->display_name }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('groups.join-request', $group) }}">
                        @csrf
                        <button class="btn-primary text-sm">Подать заявку</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
