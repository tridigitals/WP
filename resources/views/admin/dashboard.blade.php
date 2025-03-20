@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Posts Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Posts</h3>
                <i class="fas fa-file-alt text-blue-500 text-xl"></i>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total</span>
                    <span class="font-semibold">{{ number_format($stats['posts']['total']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Published</span>
                    <span class="text-green-600">{{ number_format($stats['posts']['published']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Drafts</span>
                    <span class="text-yellow-600">{{ number_format($stats['posts']['drafts']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Scheduled</span>
                    <span class="text-blue-600">{{ number_format($stats['posts']['scheduled']) }}</span>
                </div>
            </div>
            <a href="{{ route('admin.posts.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-500">
                View all posts <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Media Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Media</h3>
                <i class="fas fa-photo-video text-purple-500 text-xl"></i>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Files</span>
                    <span class="font-semibold">{{ number_format($stats['media']['total']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Storage Used</span>
                    <span class="font-semibold">{{ format_bytes($stats['media']['size']) }}</span>
                </div>
                @foreach($stats['media']['types'] as $type => $count)
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ explode('/', $type)[1] }}</span>
                        <span>{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.media.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-500">
                Manage media <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Categories & Tags -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Organization</h3>
                <i class="fas fa-tags text-green-500 text-xl"></i>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Categories</span>
                    <span class="font-semibold">{{ number_format($stats['categories']['total']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Categories</span>
                    <span>{{ number_format($stats['categories']['active']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tags</span>
                    <span class="font-semibold">{{ number_format($stats['tags']['total']) }}</span>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-600">Popular Tags:</span>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($stats['tags']['popular'] as $tag)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $tag->name }}
                            <span class="ml-1 text-blue-600">{{ $tag->count }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Users & Activity -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Users</h3>
                <i class="fas fa-users text-indigo-500 text-xl"></i>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Users</span>
                    <span class="font-semibold">{{ number_format($stats['users']['total']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Users</span>
                    <span class="text-green-600">{{ number_format($stats['users']['active']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentActivity as $activity)
                    <div class="p-6 flex space-x-4">
                        <div class="flex-shrink-0">
                            <i class="{{ $activity['icon'] }} text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                {{ $activity['description'] }}
                            </p>
                            <div class="mt-1 flex items-center text-xs text-gray-500">
                                <span>{{ $activity['user'] }}</span>
                                <span class="mx-1">&middot;</span>
                                <span>{{ $activity['time'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        No recent activity
                    </div>
                @endforelse
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">System Status</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Cache</h4>
                    <div class="mt-2 flex justify-between text-sm">
                        <span class="text-gray-500">Driver</span>
                        <span>{{ $systemStatus['cache']['driver'] }}</span>
                    </div>
                    <div class="mt-1 flex justify-between text-sm">
                        <span class="text-gray-500">Status</span>
                        <span class="{{ $systemStatus['cache']['status'] === 'Connected' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $systemStatus['cache']['status'] }}
                        </span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-900">Storage</h4>
                    <div class="mt-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Free Space</span>
                            <span>{{ format_bytes($systemStatus['storage']['free_space']) }}</span>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                            @php
                                $usedPercentage = (($systemStatus['storage']['total_space'] - $systemStatus['storage']['free_space']) / $systemStatus['storage']['total_space']) * 100;
                            @endphp
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $usedPercentage }}%"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-900">Queue</h4>
                    <div class="mt-2 flex justify-between text-sm">
                        <span class="text-gray-500">Pending Jobs</span>
                        <span>{{ number_format($systemStatus['queue']['jobs']) }}</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-900">System</h4>
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">PHP Version</span>
                            <span>{{ $systemStatus['php_version'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Laravel Version</span>
                            <span>{{ $systemStatus['laravel_version'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Maintenance Mode</span>
                            <span class="{{ $systemStatus['maintenance_mode'] ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $systemStatus['maintenance_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Quick Actions</h4>
                    <div class="space-y-2">
                        <button onclick="clearCache()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-broom mr-2"></i>
                            Clear Cache
                        </button>
                        <button onclick="optimizeMedia()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-compress-arrows-alt mr-2"></i>
                            Optimize Media
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Draft Posts -->
    @if($draftPosts->isNotEmpty())
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Drafts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($draftPosts as $post)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $post['title'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $post['author'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $post['category'] ?? 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $post['updated_at'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ $post['edit_url'] }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the cache?')) {
        axios.post('{{ route("admin.settings.cache.clear") }}')
            .then(response => {
                toastr.success('Cache cleared successfully');
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                toastr.error('Failed to clear cache');
            });
    }
}

function optimizeMedia() {
    if (confirm('Start media optimization? This may take a while.')) {
        axios.post('{{ route("admin.media.optimize") }}')
            .then(response => {
                toastr.success('Media optimization started');
            })
            .catch(error => {
                toastr.error('Failed to start media optimization');
            });
    }
}
</script>
@endpush
@endsection