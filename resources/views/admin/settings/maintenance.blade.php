@extends('layouts.admin')

@section('title', 'Maintenance Mode')

@section('content')
<div class="space-y-6">
    <!-- Maintenance Status -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Maintenance Mode Status</h3>
            <div class="flex items-center">
                <span class="mr-3 text-sm {{ $maintenanceMode['enabled'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $maintenanceMode['enabled'] ? 'Site is in maintenance mode' : 'Site is live' }}
                </span>
                <form action="{{ route('admin.settings.maintenance.toggle') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium
                                   {{ $maintenanceMode['enabled'] 
                                      ? 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                                      : 'border-transparent text-white bg-red-600 hover:bg-red-700' }}">
                        {{ $maintenanceMode['enabled'] ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Maintenance Configuration -->
    <form action="{{ route('admin.settings.maintenance.update') }}" method="POST" class="space-y-6">
        @csrf
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Configuration</h3>

            <div class="space-y-6">
                <!-- Maintenance Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">
                        Maintenance Message
                    </label>
                    <div class="mt-1">
                        <textarea id="message" name="message" rows="3" 
                                  class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        >{{ old('message', $maintenanceMode['message']) }}</textarea>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        This message will be displayed to visitors during maintenance mode.
                    </p>
                </div>

                <!-- Allowed IPs -->
                <div>
                    <label for="allowed_ips" class="block text-sm font-medium text-gray-700">
                        Allowed IP Addresses
                    </label>
                    <div class="mt-1">
                        <textarea id="allowed_ips" name="allowed_ips" rows="3" 
                                  class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Enter one IP address per line"
                        >{{ old('allowed_ips', implode("\n", $maintenanceMode['allowed_ips'])) }}</textarea>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        IP addresses that can access the site during maintenance mode. One per line.
                    </p>
                </div>

                <!-- Secret Bypass URL -->
                <div>
                    <label for="bypass_token" class="block text-sm font-medium text-gray-700">
                        Secret Bypass Token
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            {{ url('/') }}/
                        </span>
                        <input type="text" name="bypass_token" id="bypass_token" 
                               value="{{ old('bypass_token', $maintenanceMode['bypass_token'] ?? '') }}"
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        A secret URL token that allows bypassing the maintenance mode.
                    </p>
                </div>

                <!-- Advanced Options -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="retry_after" name="retry_after" type="checkbox"
                               value="1" {{ old('retry_after', $maintenanceMode['retry_after'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="retry_after" class="ml-2 block text-sm text-gray-700">
                            Send Retry-After header
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="allow_api" name="allow_api" type="checkbox"
                               value="1" {{ old('allow_api', $maintenanceMode['allow_api'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="allow_api" class="ml-2 block text-sm text-gray-700">
                            Keep API accessible
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input id="queue_jobs" name="queue_jobs" type="checkbox"
                               value="1" {{ old('queue_jobs', $maintenanceMode['queue_jobs'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="queue_jobs" class="ml-2 block text-sm text-gray-700">
                            Continue processing queue jobs
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning -->
        <div class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Important Note
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Enabling maintenance mode will make your site inaccessible to regular visitors.
                            Make sure to configure allowed IPs to maintain admin access.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Save Configuration
            </button>
        </div>
    </form>
</div>
@endsection