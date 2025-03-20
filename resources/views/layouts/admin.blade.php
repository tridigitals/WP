<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name') }} Admin</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 h-screen antialiased" x-data="{ sidebarOpen: false }">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden" 
         @click="sidebarOpen = false"
         x-cloak></div>

    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" 
         class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 lg:hidden transition duration-300 transform"
         x-cloak>
        @include('layouts.partials.sidebar')
    </div>

    <!-- Desktop Sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
        <div class="flex flex-col flex-grow bg-gray-900">
            @include('layouts.partials.sidebar')
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Top Navigation -->
        <nav class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Mobile menu button -->
                    <div class="flex items-center lg:hidden">
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-900">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-start">
                        <div class="max-w-lg w-full lg:max-w-xs">
                            <label for="search" class="sr-only">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input id="search" 
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                       placeholder="Search..." 
                                       type="search">
                            </div>
                        </div>
                    </div>

                    <!-- Right Navigation -->
                    <div class="flex items-center">
                        <!-- Notifications -->
                        <button class="p-2 text-gray-500 hover:text-gray-900">
                            <i class="fas fa-bell"></i>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open" class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <img class="h-8 w-8 rounded-full" src="{{ auth()->user()->meta_data['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}" alt="">
                                </button>
                            </div>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5" 
                                 x-cloak>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">
                                        {{ session('success') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        {{ session('error') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // CSRF Token Setup
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Toastr Configuration
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000
        };

        // Handle AJAX errors globally
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response.status === 419) {
                    toastr.error('Your session has expired. Please refresh the page.');
                } else if (error.response.status === 403) {
                    toastr.error('You do not have permission to perform this action.');
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
                return Promise.reject(error);
            }
        );
    </script>
    @stack('scripts')
</body>
</html>