@props([
    'title' => null, 
    'icon' => null, 
    'projects' => collect(),
    'showSearch' => false,
    'mode' => 'dashboard', 
])

<div class="flex bg-white border border-slate-100 shadow-sm rounded-2xl p-4 items-center justify-between mb-4">
    <!-- Left Section default search bar -->
    @if($showSearch && $projects->isNotEmpty())
        <x-search-bar :projects="$projects" :mode="$mode" />
    @else
        <div class="w-80"></div>
    @endif

    <!-- Right Section -->
    <div class="flex items-center gap-5">
        <!-- Notification -->
        <a href="#" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fa-regular fa-bell text-lg"></i>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
        </a>

        <!-- User profile -->
        <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
            <div class="text-right hidden md:block">
                <p class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ Auth::user()->role }}</p>
            </div>
            
            <!-- Avatar circle -->
            <div
                class="w-9 h-9 rounded-full overflow-hidden border border-slate-200 flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-xs shadow-sm">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>

            <form method="POST" action="{{ route('logout') }}" class="hidden" id="header-logout-form">
                @csrf
            </form>
        </div>
    </div>
</div>
