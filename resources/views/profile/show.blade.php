<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Tombol Back Solid -->
        <div class="mb-4">
            <a href="home" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 text-center">Profil Saya</h1>

                <!-- Pesan Sukses -->
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                        <p class="font-bold">Sukses!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Pesan Error -->
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                        <p class="font-bold">Error!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="flex flex-col items-center space-y-4">
                    <!-- Foto Profil -->
                    <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 ring-4 ring-blue-500 ring-offset-2">
                        @if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path))
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                 alt="Foto Profil" 
                                 class="w-full h-full object-cover">
                        @else
                            <!-- Placeholder SVG Icon -->
                            <svg class="w-full h-full text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        @endif
                    </div>
                    
                    <!-- Detail Pengguna -->
                    <div class="text-center w-full">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $user->name ?? 'N/A' }}</h2>
                        <p class="text-md text-gray-500">{{ $user->email ?? 'N/A' }}</p>
                    </div>

                    <div class="w-full bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col">
                                <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $user->kelas ?? 'Tidak ada data' }}</dd>
                            </div>
                            <div class="flex flex-col">
                                <dt class="text-sm font-medium text-gray-500">Role</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($user->role ?? 'user') }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Tombol Edit Solid -->
                <div class="mt-8 text-center">
                    <a href="{{ route('profile.edit') }}" 
                       class="inline-block w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300">
                        Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>