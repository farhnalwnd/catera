<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#e0f2fe]/10 dark:bg-zinc-950 font-sans antialiased flex h-full" x-data="{
        sidebarMini: localStorage.getItem('sidebar-mini') === 'true',
        mobileSidebarOpen: false,
        logoutDropdownOpen: false,
        toggleSidebar() {
            this.sidebarMini = !this.sidebarMini;
            localStorage.setItem('sidebar-mini', this.sidebarMini);
        }
    }">
        <!-- Desktop Sidebar -->
        <aside 
            :class="sidebarMini ? 'w-28' : 'w-64'" 
            class="hidden lg:flex flex-col h-screen sticky top-0 text-white border-e border-white/10 transition-all duration-300 ease-in-out shrink-0 select-none z-30"
            style="background: linear-gradient(180deg, #4da8cf 0%, #4da8cf 55%, #5b5856 70%, #3f8f81 100%);"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between px-4 h-20 border-b border-white/10">
                <a href="{{ route('dashboard') }}" wire:navigate 
                   :class="sidebarMini ? 'flex items-center justify-center w-full' : 'flex items-center gap-3'" 
                   class="focus:outline-none min-w-0">
                    <img src="{{ asset('storage/logo.png') }}" alt="Catera Logo" 
                         class="w-10 h-10 min-w-10 min-h-10 shrink-0 shadow-lg rounded-lg object-contain bg-white p-1">
                    <span x-show="!sidebarMini" x-transition.opacity 
                          class="font-bold text-xl text-white tracking-widest truncate uppercase">
                        CATERA
                    </span>
                </a>
                
                <button @click="toggleSidebar()" class="p-2 rounded-lg hover:bg-white/20 backdrop-blur-sm transition-colors focus:outline-none shrink-0" aria-label="Toggle Sidebar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sidebarMini ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <div class="flex-1 py-6 overflow-y-auto px-4 space-y-2">
                    @php
                        $links = [
                            ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                            ['route' => 'authorized.index', 'icon' => 'users', 'label' => 'Authorized', 'can' => 'catera:authorized:view_any'],
                            ['route' => 'quota_schedules.index', 'icon' => 'clock', 'label' => 'Quota Schedules', 'can' => 'catera:quota_scheduling:view_any'],
                            ['route' => 'access_logs.index', 'icon' => 'document-text', 'label' => 'Access Logs', 'can' => 'catera:access_logs:view_any'],
                        ];
                    @endphp

                @foreach($links as $link)
                    @if(!isset($link['can']) || auth()->user()->can($link['can']))
                        <a href="{{ route($link['route']) }}" wire:navigate 
                           class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 group focus:outline-none 
                           {{ request()->routeIs($link['route']) 
                                ? 'bg-white/20 border-l-4 border-white shadow-lg font-bold' 
                                : 'text-white/70 hover:text-white hover:bg-white/10 hover:shadow-md' }}"
                           :class="sidebarMini ? 'justify-center border-l-0' : 'border-l-4 border-transparent'"
                           title="{{ $link['label'] }}"
                        >
                            <x-dynamic-component :component="'heroicon-o-' . $link['icon']" class="w-7 h-7 shrink-0" />
                            <span x-show="!sidebarMini" class="text-lg truncate">{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/10">
                <div @click="logoutDropdownOpen = !logoutDropdownOpen" 
                     class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-white/10 transition-all duration-300"
                     :class="sidebarMini ? 'justify-center' : ''"
                >
                    <div class="flex items-center justify-center size-10 rounded-full bg-white text-[#3f8f81] font-bold text-lg shrink-0 shadow-lg">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div x-show="!sidebarMini" class="flex-1 min-w-0">
                        <div class="text-base font-bold truncate text-white">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-white/70 truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <!-- Logout Dropdown (Absolute Positioning) -->
                <div x-show="logoutDropdownOpen" 
                     @click.away="logoutDropdownOpen = false"
                     x-transition
                     class="absolute bottom-20 left-6 w-56 bg-white/30 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl py-2 z-50 text-zinc-800"
                     style="display: none;"
                >
                    <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center px-4 py-3 hover:bg-white/40 transition-colors font-medium">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-zinc-600" />
                        <span class="ml-2">Settings</span>
                    </a>
                    <form method="POST" action="{{ route('logout.app') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-4 py-3 text-red-600 hover:bg-white/40 transition-colors font-bold">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-red-600" />
                            <span class="ml-2">Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Layout Content Wrapper -->
        <div class="flex-1 flex flex-col min-h-screen min-w-0">
            <main class="flex-1 p-6 md:p-8 lg:p-10 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>

        @fluxScripts
        <x-ui.toast on="notify" />
    </body>
</html>
