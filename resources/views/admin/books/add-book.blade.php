@extends('layouts.admin-layout')

@section('title', 'Tambah atau Edit Buku')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-center mb-8">{{ isset($book) ? 'Edit Buku' : 'Tambah Buku' }}</h2>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($book) ? route('admin.books.update', $book->id) : route('admin.books.store') }}" method="POST" enctype="multipart/form-data" id="bookForm">
        @csrf
        @if(isset($book))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul Buku *</label>
                    <input type="text" name="title" id="title" class="w-full px-3 py-2 border border-gray-300 rounded-md" required value="{{ old('title', $book->title ?? '') }}">
                </div>

                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700">Penulis *</label>
                    <input type="text" name="author" id="author" class="w-full px-3 py-2 border border-gray-300 rounded-md" required value="{{ old('author', $book->author ?? '') }}">
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah *</label>
                    <input type="number" name="quantity" id="quantity" class="w-full px-3 py-2 border border-gray-300 rounded-md" required min="1" value="{{ old('quantity', $book->quantity ?? '') }}">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Kategori *</label>
                    <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Fiksi" {{ (old('category', $book->category ?? '') == 'Fiksi') ? 'selected' : '' }}>Fiksi</option>
                        <option value="Non-Fiksi" {{ (old('category', $book->category ?? '') == 'Non-Fiksi') ? 'selected' : '' }}>Non-Fiksi</option>
                        <option value="Pelajaran" {{ (old('category', $book->category ?? '') == 'Pelajaran') ? 'selected' : '' }}>Pelajaran</option>
                        <option value="Referensi" {{ (old('category', $book->category ?? '') == 'Referensi') ? 'selected' : '' }}>Referensi</option>
                        <option value="Novel" {{ (old('category', $book->category ?? '') == 'Novel') ? 'selected' : '' }}>Novel</option>
                        <option value="Komik" {{ (old('category', $book->category ?? '') == 'Komik') ? 'selected' : '' }}>Komik</option>
                        <option value="Sains" {{ (old('category', $book->category ?? '') == 'Sains') ? 'selected' : '' }}>Sains</option>
                        <option value="Sejarah" {{ (old('category', $book->category ?? '') == 'Sejarah') ? 'selected' : '' }}>Sejarah</option>
                        <option value="Teknologi" {{ (old('category', $book->category ?? '') == 'Teknologi') ? 'selected' : '' }}>Teknologi</option>
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi *</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>{{ old('description', $book->description ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="booked" class="block text-sm font-medium text-gray-700">Dipesan</label>
                        <input type="number" name="booked" id="booked" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="0" value="{{ old('booked', $book->booked ?? 0) }}">
                    </div>

                    <div>
                        <label for="borrowed" class="block text-sm font-medium text-gray-700">Dipinjam</label>
                        <input type="number" name="borrowed" id="borrowed" class="w-full px-3 py-2 border border-gray-300 rounded-md" min="0" value="{{ old('borrowed', $book->borrowed ?? 0) }}">
                    </div>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Gambar Buku</label>
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <label for="image" 
                                class="cursor-pointer inline-flex items-center px-4 py-2 border border-transparent 
                                        text-sm font-semibold rounded-full shadow-sm text-blue-700 bg-blue-50 
                                        hover:bg-blue-100 transition duration-150 ease-in-out">
                                Pilih foto profile anda
                            </label>
                            
                            <span id="file-name" class="text-sm text-gray-600 truncate max-w-xs">
                                Tidak ada file dipilih
                            </span>
                        </div>
                        
                        <input type="file" name="image" id="image" 
                            class="sr-only" 
                            accept="image/*" 
                            onchange="previewFile(this)">
                        
                        <p class="mt-1 text-xs text-gray-500">Format: JPG, JPEG, PNG. Maksimal: 2MB</p>
                    </div>
                    
                    <div id="imagePreview" class="mt-2 hidden">
                        <img id="preview" class="w-32 h-40 object-cover rounded-md">
                        <p class="text-sm text-gray-500 mt-1">Preview Gambar</p>
                    </div>
                    
                    @if(isset($book) && $book->image)
                        <div class="mt-2">
                            @if(Storage::disk('public')->exists($book->image))
                                <img src="{{ asset('storage/' . $book->image) }}" alt="Current Image" class="w-32 h-40 object-cover rounded-md">
                                <p class="text-sm text-gray-500 mt-1">Gambar saat ini</p>
                            @else
                                <p class="text-red-500 text-sm">Gambar tidak ditemukan: {{ $book->image }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            @if(isset($book))
                <a href="{{ route('admin.books.create') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">Batal</a>
            @endif
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                {{ isset($book) ? 'Update Buku' : 'Tambah Buku' }}
            </button>
        </div>
    </form>


    <div class="mt-12">
        <h3 class="text-2xl font-semibold mb-6">Daftar Buku yang Tersedia</h3>

        @if($books->isEmpty())
            <p class="text-gray-500 text-center py-8">Belum ada buku yang ditambahkan.</p>
        @else
            @foreach($books->groupBy('category') as $category => $booksByCategory)
                <div class="mb-8">
                    <h4 class="text-xl font-semibold mb-4 text-blue-600 border-b pb-2">{{ $category }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach($booksByCategory as $bookItem)
                            <div class="flex flex-col border rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                                <div class="h-64 w-full bg-gray-200 overflow-hidden flex items-center justify-center">
                                    @if($bookItem->image && Storage::disk('public')->exists($bookItem->image))
                                        <img src="{{ asset('storage/' . $bookItem->image) }}" 
                                             alt="{{ $bookItem->title }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="p-4 flex-1 flex flex-col justify-between">
                                    <h5 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">{{ $bookItem->title }}</h5>
                                    <p class="text-sm text-gray-600 mb-1 line-clamp-1">Penulis: {{ $bookItem->author }}</p>
                                    <p class="text-sm text-gray-600 mb-2">Stok: {{ $bookItem->quantity - $bookItem->borrowed - $bookItem->booked }} / {{ $bookItem->quantity }}</p>
                                    <p class="text-xs text-gray-500 mb-2">Dipinjam: {{ $bookItem->borrowed }}, Dipesan: {{ $bookItem->booked }}</p>

                                    <div class="mt-auto flex justify-between items-center w-full">
                                        <a href="{{ route('admin.books.edit', $bookItem->id) }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">Edit</a>
                                        
                                        <form action="{{ route('admin.books.destroy', $bookItem->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
    }
}

// Reset preview ketika form direset
document.getElementById('bookForm').addEventListener('reset', function() {
    document.getElementById('imagePreview').classList.add('hidden');
});
</script>
@endsection