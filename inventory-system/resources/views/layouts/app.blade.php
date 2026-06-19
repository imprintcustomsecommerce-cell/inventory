<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-gray-900 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">📦 Imprint Inventory</h1>
                    </div>
                    <div class="flex gap-6">
                        <a href="{{ route('inventory.index') }}"
                           class="px-3 py-2 rounded-md {{ request()->routeIs('inventory.index') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800' }}">
                            Inventory
                        </a>
                        <a href="{{ route('inventory.lowStock') }}"
                           class="px-3 py-2 rounded-md {{ request()->routeIs('inventory.lowStock') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800' }}">
                            Low Stock
                        </a>
                        <a href="{{ route('inventory.allMovements') }}"
                           class="px-3 py-2 rounded-md {{ request()->routeIs('inventory.allMovements') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800' }}">
                            Report
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white text-center py-4 mt-8">
            <p>&copy; 2026 Imprint Customs Inventory System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
