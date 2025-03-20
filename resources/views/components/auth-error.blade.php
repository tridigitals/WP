<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full space-y-8 p-8 bg-white shadow-lg rounded-lg">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-red-500">
                <i class="fas fa-lock text-4xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                {{ $title ?? 'Access Denied' }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ $message ?? 'You do not have permission to access this resource.' }}
            </p>
        </div>

        <div class="mt-8 space-y-6">
            @if(isset($action))
                <div class="text-center">
                    <a href="{{ $action['url'] }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ $action['text'] }}
                    </a>
                </div>
            @endif

            <div class="text-center">
                @if(auth()->check())
                    <p class="text-sm text-gray-500">
                        Logged in as: {{ auth()->user()->name }}
                        <br>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-500">
                                Switch Account
                            </button>
                        </form>
                    </p>
                @else
                    <p class="text-sm text-gray-500">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500">
                            Log in with a different account
                        </a>
                    </p>
                @endif
            </div>

            @if(isset($help))
                <div class="mt-4 bg-gray-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Need Help?
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>{{ $help }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(config('app.debug'))
                <div class="mt-4 text-xs text-gray-500">
                    <p class="font-semibold">Debug Information:</p>
                    <pre class="mt-1 bg-gray-100 p-2 rounded overflow-x-auto">
Route: {{ request()->route()->getName() }}
User ID: {{ auth()->id() ?? 'Guest' }}
Permissions: {{ auth()->check() ? implode(', ', array_keys(auth()->user()->getAbilities())) : 'None' }}
                    </pre>
                </div>
            @endif
        </div>
    </div>
</div>