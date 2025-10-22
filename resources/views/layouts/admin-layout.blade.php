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

        /* Default style for navigation item (Status TIDAK AKTIF) */
        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #ffffff;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-weight: 500; /* STANDAR NON-AKTIF (Medium) */
        }
        
        /* Style for active navigation item (Status AKTIF) */
        .nav-item.active {
            background-color: #2563EB; /* Darker blue */
            color: #e6e6e6 !important;
            border-radius: 0.375rem;
            font-weight: 700; /* TEBAL (Bold) */
        }

        /* Hover style for navigation item */
        .nav-item:hover:not(.active) {
            background-color: #3B82F6;
            color: #ffffff;
            border-radius: 0.375rem;
        }

        /* Style for active dropdown item */
        .dropdown-item.active-dropdown {
            background-color: #EFF6FF; /* bg-blue-50 */
            color: #2563EB; /* text-blue-600 */
            font-weight: 600; /* Semibold/medium for visibility */
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
                            
                            @php
                                $profileActive = Request::routeIs('profile.show');
                                $historyActive = Request::routeIs('history.show') || Request::is('history*');
                            @endphp
                            
                            <a href="{{ route('profile.show') }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 {{ $profileActive ? 'active-dropdown' : '' }}" role="menuitem">Profile Saya</a>
                            
                            <!-- Perbaikan Dropdown History -->
                            <a href="{{ route('history.show') }}" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 {{ $historyActive ? 'active-dropdown' : '' }}" role="menuitem">History</a>
                            
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
                
                @php
                    // Helper yang lebih stabil untuk menentukan status aktif navigasi
                    function isActive($name) {
                        if (is_array($name)) {
                            foreach ($name as $n) {
                                if (Request::routeIs($n)) {
                                    return true;
                                }
                            }
                            return false;
                        }
                        return Request::routeIs($name);
                    }
                @endphp
                
                <!-- Data Pengguna (Users) -->
                <a href="{{ route('admin.index') }}" class="nav-item text-white flex items-center space-x-2 transition-colors duration-300 {{ isActive('admin.index') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2c0-.656-.126-1.283-.356-1.857M21 12v3.388m0 0s-2.625-1.5-6.875-1.5M15 15.388v-3.388c0-.621-.303-1.092-.857-1.464M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2zM9 13a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    <span>Data Pengguna</span>
                </a>
                
                <!-- Manajemen Buku (Books) - Menggunakan ikon buku dari app.blade.php -->
                <a href="{{ route('admin.books.create') }}" class="nav-item text-white flex items-center space-x-2 transition-colors duration-300 {{ isActive(['admin.books.create', 'admin.books.edit', 'admin.books.index']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Manajemen Buku</span>
                </a>
                
                <!-- Dipesan (Booked) - Menggunakan ikon bookmark dari app.blade.php -->
                <a href="{{ route('admin.books.booked') }}" class="nav-item text-white flex items-center space-x-2 transition-colors duration-300 {{ isActive('admin.books.booked') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    <span>Dipesan</span>
                </a>
                
                <!-- Dipinjam (Borrowed) - Menggunakan ikon folder/file dari app.blade.php -->
                <a href="{{ route('admin.books.borrowed') }}" class="nav-item text-white flex items-center space-x-2 transition-colors duration-300 {{ isActive('admin.books.borrowed') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0-8H8m0 0h8m-9 8h9" />
                    </svg>
                    <span>Dipinjam</span>
                </a>
                
                <!-- Denda (Penalty) - Menggunakan ikon alert/exclamation dari app.blade.php -->
                <a href="{{ route('admin.penalty') }}" class="nav-item text-white flex items-center space-x-2 transition-colors duration-300 {{ isActive('admin.penalty') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            
            // SweetAlert notifications
            // PERBAIKAN: Gunakan fungsi e() untuk mengamankan string Blade
             @if(session('alert_type') && session('alert_title'))
                Swal.fire({
                    icon: "{!! e(session('alert_type')) !!}",
                    title: "{!! e(session('alert_title')) !!}",
                    text: "{!! e(session('alert_message', '')) !!}",
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
                    text: "{!! e(session('error')) !!}",
                    confirmButtonColor: '#EF4444',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{!! e(session('success')) !!}",
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
</body>
</html>
