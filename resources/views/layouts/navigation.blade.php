<nav class="border-b border-stone-200/80 bg-white/75 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Панель
                    </x-nav-link>
                    <x-nav-link :href="route('topics.index')" :active="request()->routeIs('topics.*')">
                        Темы
                    </x-nav-link>
                    <x-nav-link :href="route('thesis.my')" :active="request()->routeIs('thesis.my')">
                        Моя работа
                    </x-nav-link>
                    @if (Auth::user()->isSupervisor() || Auth::user()->isAdmin() || Auth::user()->isCommission() || Auth::user()->isReviewer())
                        <x-nav-link :href="route('thesis.index')" :active="request()->routeIs('thesis.index') || request()->routeIs('thesis.show')">
                            Работы
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->isAdmin())
                        <x-nav-link :href="route('admin.groups.index')" :active="request()->routeIs('admin.groups.*')">
                            Группы
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            Пользователи
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->isSupervisor())
                        @php($group = Auth::user()->supervisedGroups()->first())
                        @if ($group)
                            <x-nav-link :href="route('groups.show', $group)" :active="request()->routeIs('groups.show') || request()->routeIs('admin.groups.show')">
                                Моя группа
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <div class="hidden items-center gap-3 sm:flex sm:ms-6">
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-300 hover:text-stone-900 {{ request()->routeIs('profile.*') ? 'ring-2 ring-amber-200' : '' }}"
                    title="Профиль"
                >
                    {{ Auth::user()->display_name }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-sm">
                        Выйти
                    </button>
                </form>
            </div>

            <details class="group relative -me-2 sm:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:outline-none [&::-webkit-details-marker]:hidden">
                    <svg class="h-6 w-6 group-open:hidden" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="hidden h-6 w-6 group-open:block" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </summary>

                <div class="absolute end-0 top-full z-50 mt-2 w-[min(20rem,calc(100vw-2rem))] rounded-2xl border border-stone-200 bg-white p-2 shadow-lg">
                    <div class="space-y-1">
                        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Панель
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('topics.index')" :active="request()->routeIs('topics.*')">
                            Темы
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('thesis.my')" :active="request()->routeIs('thesis.my')">
                            Моя работа
                        </x-responsive-nav-link>
                        @if (Auth::user()->isSupervisor() || Auth::user()->isAdmin() || Auth::user()->isCommission() || Auth::user()->isReviewer())
                            <x-responsive-nav-link :href="route('thesis.index')" :active="request()->routeIs('thesis.index') || request()->routeIs('thesis.show')">
                                Работы
                            </x-responsive-nav-link>
                        @endif
                        @if (Auth::user()->isAdmin())
                            <x-responsive-nav-link :href="route('admin.groups.index')" :active="request()->routeIs('admin.groups.*')">
                                Группы
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                Пользователи
                            </x-responsive-nav-link>
                        @endif
                        @if (Auth::user()->isSupervisor())
                            @php($group = Auth::user()->supervisedGroups()->first())
                            @if ($group)
                                <x-responsive-nav-link :href="route('groups.show', $group)" :active="request()->routeIs('groups.show') || request()->routeIs('admin.groups.show')">
                                    Моя группа
                                </x-responsive-nav-link>
                            @endif
                        @endif
                    </div>

                    <div class="mt-2 border-t border-stone-200 pt-2">
                        <div class="px-3 py-2">
                            <div class="text-sm font-medium text-stone-800">{{ Auth::user()->display_name }}</div>
                            <div class="text-xs text-stone-500">{{ Auth::user()->email }}</div>
                        </div>
                        <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                            Профиль
                        </x-responsive-nav-link>
                        <form method="POST" action="{{ route('logout') }}" class="px-1 py-1">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-stone-700 transition hover:bg-stone-100">
                                Выйти
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        </div>
    </div>
</nav>
