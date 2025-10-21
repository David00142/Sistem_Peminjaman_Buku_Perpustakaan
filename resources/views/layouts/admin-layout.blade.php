<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Dashboard') | BOBOOK</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .brand-font {
            font-family: 'Merriweather', serif;
        }
        .nav-item {
            position: relative;
            padding-bottom: 2px;
        }
        .nav-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #3B82F6;
            transition: width 0.3s ease;
        }
        .nav-item:hover::after, .nav-item.active::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h1 class="brand-font text-2xl font-bold text-blue-800">BOBOOK</h1>
                </div>
                <div class="relative">
                    <button class="flex items-center space-x-2 focus:outline-none" id="profileButton" aria-haspopup="true" aria-expanded="false">
                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                            @if(Auth::check())
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @else
                                G
                            @endif
                        </div>
                        <div class="text-left hidden md:block">
                            @if(Auth::check())
                                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">
                                    @if(Auth::user()->role === 'pustakawan')
                                        Admin Pustakawan
                                    @else
                                        Anggota Perpustakaan
                                    @endif
                                </p>
                            @else
                                <p class="text-sm font-medium text-gray-800">Guest</p>
                                <p class="text-xs text-gray-500">Not Logged In</p>
                            @endif
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50 hidden" role="menu" aria-orientation="vertical" aria-labelledby="profileButton">
                        @if(Auth::check())
                            <div class="px-4 py-2 border-b">
                                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600" role="menuitem">Profile Saya</a>
                            <a href="{{ route('history.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600" role="menuitem">History</a>
                            <a href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600" role="menuitem">
                                Keluar
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600" role="menuitem">Login</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600" role="menuitem">Register</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="bg-blue-600 shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-center space-x-12 py-3">
                <a href="{{ route('admin.index') }}" class="nav-item text-white font-medium flex items-center space-x-2 hover:text-blue-200 transition-colors duration-300 {{ Request::routeIs('admin.index') ? 'active' : '' }}">
                    <span>Data Pengguna</span>
                </a>
                <a href="{{ route('admin.books.create') }}" class="nav-item text-white font-medium flex items-center space-x-2 hover:text-blue-200 transition-colors duration-300 {{ Request::routeIs('admin.books.create') ? 'active' : '' }}">
                    <span>Tambah Buku</span>
                </a>
                <a href="{{ route('admin.books.booked') }}" class="nav-item text-white font-medium flex items-center space-x-2 hover:text-blue-200 transition-colors duration-300 {{ Request::routeIs('admin.books.booked') ? 'active' : '' }}">
                    <span>Dipesan</span>
                </a>
                <a href="{{ route('admin.books.borrowed') }}" class="nav-item text-white font-medium flex items-center space-x-2 hover:text-blue-200 transition-colors duration-300 {{ Request::routeIs('admin.books.borrowed') ? 'active' : '' }}">
                    <span>Dipinjam</span>
                </a>
                <a href="{{ route('admin.penalty') }}" class="nav-item text-white font-medium flex items-center space-x-2 hover:text-blue-200 transition-colors duration-300 {{ Request::routeIs('admin.penalty') ? 'active' : '' }}">
                    <span>Denda</span>
                </a>
            </div>
        </div>
    </nav>
    
    <main class="container mx-auto px-4 py-6 flex-grow">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h2 class="brand-font text-xl font-bold">BOBOOK</h2>
                    <p class="text-sm text-gray-400">Sistem Peminjaman Buku Perpustakaan</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-400">&copy; {{ date('Y') }} BOBOOK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profileButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            let isDropdownOpen = false;

            profileButton.addEventListener('click', function() {
                isDropdownOpen = !isDropdownOpen;
                dropdownMenu.classList.toggle('hidden');
                profileButton.setAttribute('aria-expanded', isDropdownOpen);
            });

            // Close dropdown if user clicks outside
            window.addEventListener('click', function(e) {
                if (!profileButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                    profileButton.setAttribute('aria-expanded', 'false');
                    isDropdownOpen = false;
                }
            });
        });
    </script>
</body>
</html>