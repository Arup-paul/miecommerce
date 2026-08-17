<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Simple Ecommerce') }} @hasSection('title') - @yield('title') @endif</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <script src="{{ mix('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-800">
                    {{ config('app.name', 'Simple Ecommerce') }}
                </a>

                <nav class="flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900">Shop</a>
                    <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-gray-900">
                        Cart
                        @if (session('cart') && count(session('cart')))
                            <span class="inline-flex items-center justify-center rounded-full bg-indigo-600 text-white text-xs w-5 h-5 ml-1">{{ count(session('cart')) }}</span>
                        @endif
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                    @endguest

                    @auth
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">Admin</a>
                        @endif

                        <span class="text-gray-600">{{ Auth::user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                        </form>
                    @endauth
                </nav>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <aside class="w-full md:w-56 shrink-0">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Categories</h2>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                All Products
                            </a>
                        </li>
                        @foreach ($navCategories ?? [] as $navCategory)
                            <li>
                                <a href="{{ route('categories.show', $navCategory) }}" class="block px-3 py-2 rounded-md text-sm {{ optional(request()->route('category'))->id === $navCategory->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                    {{ $navCategory->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                <main class="flex-1">
                    @if (session('status'))
                        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
