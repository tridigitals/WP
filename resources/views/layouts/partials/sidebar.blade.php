<div class="flex flex-col h-full">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 flex-shrink-0 px-4 bg-gray-900">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
            <img class="h-8 w-auto" src="{{ asset('images/logo-white.png') }}" alt="{{ config('app.name') }}">
            <span class="ml-2 text-xl font-bold text-white">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-home w-6 h-6 mr-3"></i>
            Dashboard
        </a>

        <!-- Content Management -->
        <div x-data="{ open: {{ request()->routeIs('admin.posts.*', 'admin.categories.*', 'admin.tags.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <i class="fas fa-file-alt w-6 h-6 mr-3"></i>
                <span class="flex-1">Content</span>
                <i class="fas" :class="{ 'fa-chevron-down': open, 'fa-chevron-right': !open }"></i>
            </button>
            <div x-show="open" class="space-y-1 pl-8" x-cloak>
                <a href="{{ route('admin.posts.index') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.posts.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Posts
                </a>
                <a href="{{ route('admin.categories.index') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Categories
                </a>
                <a href="{{ route('admin.tags.index') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.tags.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Tags
                </a>
            </div>
        </div>

        <!-- Media -->
        <a href="{{ route('admin.media.index') }}" 
           class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.media.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-photo-video w-6 h-6 mr-3"></i>
            Media
        </a>

        <!-- SEO -->
        <div x-data="{ open: {{ request()->routeIs('admin.seo.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <i class="fas fa-search w-6 h-6 mr-3"></i>
                <span class="flex-1">SEO</span>
                <i class="fas" :class="{ 'fa-chevron-down': open, 'fa-chevron-right': !open }"></i>
            </button>
            <div x-show="open" class="space-y-1 pl-8" x-cloak>
                <a href="{{ route('admin.seo.index') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.seo.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Meta Information
                </a>
                <a href="{{ route('admin.seo.analyze') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.seo.analyze') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    SEO Analysis
                </a>
            </div>
        </div>

        <!-- Settings -->
        <div x-data="{ open: {{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <i class="fas fa-cog w-6 h-6 mr-3"></i>
                <span class="flex-1">Settings</span>
                <i class="fas" :class="{ 'fa-chevron-down': open, 'fa-chevron-right': !open }"></i>
            </button>
            <div x-show="open" class="space-y-1 pl-8" x-cloak>
                <a href="{{ route('admin.settings.index') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    General
                </a>
                <a href="{{ route('admin.settings.cache') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.cache') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Cache
                </a>
                <a href="{{ route('admin.settings.maintenance') }}" 
                   class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.maintenance') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    Maintenance
                </a>
            </div>
        </div>
    </nav>

    <!-- User Info -->
    <div class="flex-shrink-0 flex bg-gray-800 p-4">
        <div class="flex items-center w-full">
            <div class="flex-shrink-0">
                <img class="h-8 w-8 rounded-full" 
                     src="{{ auth()->user()->meta_data['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}" 
                     alt="{{ auth()->user()->name }}">
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs font-medium text-gray-300">
                    Administrator
                </p>
            </div>
        </div>
    </div>
</div>