<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Imprint Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-400 rounded-lg mb-4">
                    <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white">Imprint Inventory</h1>
                <p class="text-gray-400 mt-2">Professional Inventory Management</p>
            </div>

            <!-- Login Form -->
            <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
                <h2 class="text-2xl font-bold text-white mb-6">Welcome Back</h2>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-900 border border-red-700 rounded-lg">
                        <p class="text-red-200 text-sm font-semibold">Login failed:</p>
                        <ul class="text-red-200 text-sm mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-20 transition @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-20 transition @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 bg-gray-700 border-gray-600 rounded accent-yellow-400">
                        <label for="remember" class="ml-2 text-sm text-gray-400">Remember me</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-3 rounded-lg transition duration-200 transform hover:scale-105">
                        Sign In
                    </button>
                </form>

                <!-- Divider -->
                <div class="my-6 flex items-center">
                    <div class="flex-1 border-t border-gray-600"></div>
                    <span class="px-3 text-gray-400 text-sm">OR</span>
                    <div class="flex-1 border-t border-gray-600"></div>
                </div>

                <!-- Register Link -->
                <p class="text-center text-gray-400">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-yellow-400 hover:text-yellow-300 font-semibold transition">
                        Create one here
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <p class="text-center text-gray-500 text-sm mt-8">
                © 2026 Imprint Customs. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
