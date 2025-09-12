<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'BOBOOK - Sistem Peminjaman Buku')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .brand-font {
            font-family: 'Merriweather', serif;
        }
        /* Style for active navigation item */
        .nav-item.active {
            background-color: #2563EB; /* A slightly lighter blue than the background, but still distinct */
            color: #ffffff; /* White text for active link */
            border-radius: 0.375rem; /* Rounded corners */
            padding: 0.5rem 1rem; /* Adjust padding */
        }
        /* Default style for navigation item */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #ffffff; /* Default white text */
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        /* Hover style for navigation item */
        .nav-item:hover:not(.active) { /* Apply hover only if not active */
            background-color: #3B82F6; /* Blue hover for all links */
            color: #ffffff;
            border-radius: 0.375rem;
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

                <div class="flex-1 mx-8 hidden md:block">
                    <div class="relative">
                        <input type="text" placeholder="Cari buku, penulis, atau kategori..." 
                               class="w-full py-2 px-4 pr-10 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button class="absolute right-3 top-2 text-gray-400 hover:text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <button class="flex items-center space-x-2 focus:outline-none" id="profileButton">
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

                    <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50 hidden">
                        @if(Auth::check())
                            <div class="px-4 py-2 border-b">
                                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Detail Profile</a>
                            <a href="{{ route('history.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">My History</a>
                            <a href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Login</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Register</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <nav class="bg-blue-600 shadow-md">
            <div class="container mx-auto px-4">
                <div class="flex justify-center space-x-4 py-2">
                    <a href="{{ route('home') }}" class="nav-item text-white font-medium
                    {{ Request::routeIs('home') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('available') }}" class="nav-item text-white font-medium
                    {{ Request::routeIs('available') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Available Books</span>
                    </a>
                    <a href="{{ route('booked') }}" class="nav-item text-white font-medium
                    {{ Request::routeIs('booked') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <span>Booked</span>
                    </a>
                    <a href="{{ route('borrowed-books') }}" class="nav-item text-white font-medium
                    {{ Request::routeIs('borrowed-books') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0-8H8m0 0h8m-9 8h9" />
                        </svg>
                        <span>Borrowed</span>
                    </a>
                    <a href="{{ route('penalty') }}" class="nav-item text-white font-medium
                    {{ Request::routeIs('penalty') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Penalty</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-6 flex-grow">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h2 class="brand-font text-xl font-bold">BOBOOK</h2>
                    <p class="text-sm text-gray-400">Sistem Peminjaman Buku Perpustakaan</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-400">&copy; 2025 BOBOOK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // JavaScript to toggle dropdown visibility on click
        document.getElementById('profileButton').addEventListener('click', function() {
            const dropdownMenu = document.getElementById('dropdownMenu');
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const profileButton = document.getElementById('profileButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // SweetAlert notifications
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('alert_type') && session('alert_title'))
                Swal.fire({
                    icon: '{{ session('alert_type') }}',
                    title: '{{ session('alert_title') }}',
                    text: '{{ session('alert_message', '') }}',
                    confirmButtonColor: '#3B82F6',
                    confirmButtonText: 'OK',
                    timer: 5000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#EF4444',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
</body>
</html>