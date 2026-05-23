<nav x-data="{ open: false }" class="border-b border-stone-200/80 bg-white/75 backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
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

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-600 transition hover:text-stone-900">
                            <div>{{ Auth::user()->full_name ?: Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
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

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->full_name ?: Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
