@extends('layouts.admin')

@section('title', 'Cache Management')

@section('content')
<div class="space-y-6">
    <!-- Cache Overview -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Cache Overview</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cache Status -->
            <div>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cache Status</dt>
                            <dd class="flex items-baseline">
                                @if($cacheStats['enabled'])
                                    <div class="text-sm font-semibold text-green-600">Enabled</div>
                                @else
                                    <div class="text-sm font-semibold text-red-600">Disabled</div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cache Driver -->
            <div>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cache Driver</dt>
                            <dd class="flex items-baseline">
                                <div class="text-sm font-semibold text-gray-900">{{ ucfirst($cacheStats['driver']) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cache Size -->
            <div>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-hdd text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Cache Size</dt>
                            <dd class="flex items-baseline">
                                <div class="text-sm font-semibold text-gray-900">{{ $cacheStats['size'] }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cache Duration -->
            <div>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Default Duration</dt>
                            <dd class="flex items-baseline">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $cacheStats['duration'] / 60 }} minutes
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Actions -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Cache Management</h3>

        <div class="space-y-4">
            <!-- Clear Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <form action="{{ route('admin.cache.clear', ['type' => 'all']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Clear All Cache
                        </button>
                    </form>
                </div>

                <div>
                    <form action="{{ route('admin.cache.clear', ['type' => 'views']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-2"></i>
                            Clear View Cache
                        </button>
                    </form>
                </div>

                <div>
                    <form action="{{ route('admin.cache.clear', ['type' => 'routes']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-route mr-2"></i>
                            Clear Route Cache
                        </button>
                    </form>
                </div>

                <div>
                    <form action="{{ route('admin.cache.clear', ['type' => 'config']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-cog mr-2"></i>
                            Clear Config Cache
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cache Warning -->
            <div class="rounded-md bg-yellow-50 p-4 mt-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Cache Clear Warning</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Clearing the cache may temporarily impact site performance until the cache is rebuilt. Use with caution on production sites.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection