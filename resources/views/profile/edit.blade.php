<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
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
            <a href="{{ route('profile.show') }}" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Profil
            </a>
        </div>

        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6 text-center">Edit Profil</h1>

                <!-- Pesan Error -->
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                        <p class="font-bold">Terdapat kesalahan:</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-6">
                        <!-- Nama (Tidak bisa diedit) -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" disabled
                                   class="mt-1 block w-full px-3 py-2 bg-gray-200 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Nama tidak dapat diubah.</p>
                        </div>
                        
                        <!-- Kelas (Tidak bisa diedit) -->
                        <div>
                            <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                            <input type="text" id="kelas" name="kelas" value="{{ old('kelas', $user->kelas) }}" disabled
                                   class="mt-1 block w-full px-3 py-2 bg-gray-200 border border-gray-300 rounded-md shadow-sm cursor-not-allowed focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Kelas tidak dapat diubah.</p>
                        </div>

                        <!-- Email (Bisa diedit) -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload Foto Profil -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ganti Foto Profil</label>
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="inline-block h-16 w-16 rounded-full overflow-hidden bg-gray-100">
                                    @if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path))
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                             alt="Foto Profil" 
                                             class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    @endif
                                </span>
                                <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <label for="profile_photo" 
                                        class="cursor-pointer inline-flex items-center px-4 py-2 border border-transparent 
                                                text-sm font-semibold rounded-full shadow-sm text-blue-700 bg-blue-50 
                                                hover:bg-blue-100 transition duration-150 ease-in-out">
                                        Pilih foto profile anda
                                    </label>
                                    
                                    <span id="file-name" class="text-sm text-gray-600 truncate max-w-xs">
                                        Tidak ada file dipilih
                                    </span>
                                </div>

    
    <input type="file" name="profile_photo" id="profile_photo" 
           class="sr-only"> <p class="mt-1 text-xs text-gray-500">Format: JPG, JPEG, PNG. Maksimal: 2MB</p>
</div>
                            </div>
                            @error('profile_photo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-end space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="{{ route('profile.show') }}" 
                           class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-300 text-center">
                            Batal
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>