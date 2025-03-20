@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'general' }">
    <!-- Tabs -->
    <nav class="flex space-x-4 border-b">
        <button @click="activeTab = 'general'"
                :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'general'}"
                class="px-3 py-2 text-sm font-medium">
            General
        </button>
        <button @click="activeTab = 'media'"
                :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'media'}"
                class="px-3 py-2 text-sm font-medium">
            Media
        </button>
        <button @click="activeTab = 'seo'"
                :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'seo'}"
                class="px-3 py-2 text-sm font-medium">
            SEO
        </button>
        <button @click="activeTab = 'cache'"
                :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'cache'}"
                class="px-3 py-2 text-sm font-medium">
            Cache
        </button>
        <button @click="activeTab = 'security'"
                :class="{'border-b-2 border-blue-500 text-blue-600': activeTab === 'security'}"
                class="px-3 py-2 text-sm font-medium">
            Security
        </button>
    </nav>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('POST')

        <!-- General Settings -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <input type="text" name="site_name" id="site_name" 
                               value="{{ old('site_name', $settings['general']['site_name']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('site_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="site_description" class="block text-sm font-medium text-gray-700">Site Description</label>
                        <textarea name="site_description" id="site_description" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('site_description', $settings['general']['site_description']) }}</textarea>
                        @error('site_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="posts_per_page" class="block text-sm font-medium text-gray-700">Posts Per Page</label>
                        <input type="number" name="posts_per_page" id="posts_per_page" 
                               value="{{ old('posts_per_page', $settings['general']['posts_per_page']) }}"
                               class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm">
                        @error('posts_per_page')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="allow_comments" id="allow_comments" 
                               value="1" {{ old('allow_comments', $settings['general']['allow_comments']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="allow_comments" class="ml-2 block text-sm text-gray-700">Allow Comments</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media Settings -->
        <div x-show="activeTab === 'media'" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Media Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="media_max_size" class="block text-sm font-medium text-gray-700">Maximum Upload Size (KB)</label>
                        <input type="number" name="media_max_size" id="media_max_size" 
                               value="{{ old('media_max_size', $settings['media']['max_file_size']) }}"
                               class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm">
                        @error('media_max_size')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Allowed File Types</label>
                        <div class="mt-2 space-y-2">
                            @foreach(['image', 'document', 'audio', 'video'] as $type)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="media_allowed_types[]" value="{{ $type }}"
                                               {{ in_array($type, old('media_allowed_types', $settings['media']['allowed_file_types'])) ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">{{ ucfirst($type) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="optimize_images" id="optimize_images" 
                               value="1" {{ old('optimize_images', $settings['media']['optimize_images']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="optimize_images" class="ml-2 block text-sm text-gray-700">Optimize Images on Upload</label>
                    </div>

                    <!-- Storage Usage -->
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Storage Usage</h4>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            @php
                                $percentage = min(($settings['media']['storage_used'] / $settings['media']['storage_limit']) * 100, 100);
                            @endphp
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $settings['media']['storage_used'] }} of {{ $settings['media']['storage_limit'] }} used
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Settings -->
        <div x-show="activeTab === 'seo'" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="enable_meta" id="enable_meta" 
                               value="1" {{ old('enable_meta', $settings['seo']['enable_meta']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="enable_meta" class="ml-2 block text-sm text-gray-700">Enable Meta Information</label>
                    </div>

                    <div>
                        <label for="default_meta_title" class="block text-sm font-medium text-gray-700">Default Meta Title</label>
                        <input type="text" name="default_meta_title" id="default_meta_title" 
                               value="{{ old('default_meta_title', $settings['seo']['default_meta_title']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <p class="mt-1 text-sm text-gray-500">Maximum 60 characters</p>
                        @error('default_meta_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_meta_description" class="block text-sm font-medium text-gray-700">Default Meta Description</label>
                        <textarea name="default_meta_description" id="default_meta_description" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('default_meta_description', $settings['seo']['default_meta_description']) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Maximum 160 characters</p>
                        @error('default_meta_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="enable_schema_org" id="enable_schema_org" 
                               value="1" {{ old('enable_schema_org', $settings['seo']['enable_schema_org']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="enable_schema_org" class="ml-2 block text-sm text-gray-700">Enable Schema.org Markup</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="enable_twitter_cards" id="enable_twitter_cards" 
                               value="1" {{ old('enable_twitter_cards', $settings['seo']['enable_twitter_cards']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="enable_twitter_cards" class="ml-2 block text-sm text-gray-700">Enable Twitter Cards</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Settings -->
        <div x-show="activeTab === 'cache'" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cache Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="cache_enabled" id="cache_enabled" 
                               value="1" {{ old('cache_enabled', $settings['cache']['enabled']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="cache_enabled" class="ml-2 block text-sm text-gray-700">Enable Caching</label>
                    </div>

                    <div>
                        <label for="cache_duration" class="block text-sm font-medium text-gray-700">Cache Duration (minutes)</label>
                        <input type="number" name="cache_duration" id="cache_duration" 
                               value="{{ old('cache_duration', $settings['cache']['duration'] / 60) }}"
                               class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm">
                        @error('cache_duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.cache.clear') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Clear Cache
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div x-show="activeTab === 'security'" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="enable_csrf" id="enable_csrf" 
                               value="1" {{ old('enable_csrf', $settings['security']['enable_csrf']) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <label for="enable_csrf" class="ml-2 block text-sm text-gray-700">Enable CSRF Protection</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Allowed HTML Tags</label>
                        <div class="space-y-2">
                            @foreach($settings['security']['allowed_html_tags'] as $tag)
                                <div class="flex items-center">
                                    <input type="checkbox" name="allowed_html_tags[]" value="{{ $tag }}"
                                           {{ in_array($tag, old('allowed_html_tags', $settings['security']['allowed_html_tags'])) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 text-blue-600">
                                    <label class="ml-2 text-sm text-gray-700">&lt;{{ $tag }}&gt;</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reset
            </button>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection