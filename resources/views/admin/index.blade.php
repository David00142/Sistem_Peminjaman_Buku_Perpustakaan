@extends('layouts.admin-layout')

@section('title', 'Admin - Kelola Pengguna')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Kelola Pengguna</h2>

    <!-- Menampilkan pesan sukses jika ada -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Tabel Daftar Pengguna -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama</th>
                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kelas</th>
                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Role</th>
                    <th class="py-4 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-4 px-6">{{ $user->name }}</td>
                        <td class="py-4 px-6">{{ $user->email }}</td>
                        <td class="py-4 px-6">{{ $user->kelas ?? '-' }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($user->role == 'admin') bg-red-100 text-red-800
                                @elseif($user->role == 'pustakawan') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex space-x-2">
                                <!-- Tombol Edit -->
                                <button onclick="openEditModal({{ $user }})" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Edit
                                </button>
                                
                                <!-- Update Role Form -->
                                <form action="{{ route('admin.updateRole', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" 
                                            class="border rounded py-1 px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="pustakawan" {{ $user->role == 'pustakawan' ? 'selected' : '' }}>Pustakawan</option>
                                        <option value="anggota" {{ $user->role == 'anggota' ? 'selected' : '' }}>Anggota</option>
                                    </select>
                                </form>

                                <!-- Delete User Form -->
                                <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus user ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-xl font-semibold text-gray-800">Edit Pengguna</h3>
        </div>
<form id="editForm" method="POST" action="">
    @csrf
    @method('PUT') <!-- Pastikan menggunakan PUT method spoofing -->

    <div class="px-6 py-4 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="name" id="editName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" id="editEmail" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Pilihan Kelas -->
        <div>
            <label for="kelas" class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas</label>
            <select name="kelas" id="editKelas" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('kelas') border-red-500 @enderror" 
                    required>
                <optgroup label="X">
                    <option value="X TKJ1">X TKJ 1</option>
                    <option value="X TKJ2">X TKJ 2</option>
                    <option value="X TKJ3">X TKJ 3</option>
                    <option value="X AK1">X AK1</option>
                    <option value="X AK2">X AK2</option>
                    <option value="X BID1">X BID1</option>
                </optgroup>
                <optgroup label="XI">
                    <option value="XI TKJ1">XI TKJ 1</option>
                    <option value="XI TKJ2">XI TKJ 2</option>
                    <option value="XI TKJ3">XI TKJ 3</option>
                    <option value="XI AK1">XI AK 1</option>
                    <option value="XI AK2">XI AK 2</option>
                    <option value="XI BID1">XI BID 1</option>
                </optgroup>
                <optgroup label="XII">
                    <option value="XII TKJ1">XII TKJ 1</option>
                    <option value="XII TKJ2">XII TKJ 2</option>
                    <option value="XII TKJ3">XII TKJ 3</option>
                    <option value="XII AK1">XII AK 1</option>
                    <option value="XII AK2">XII AK 2</option>
                    <option value="XII BID1">XII BID 1</option>
                </optgroup>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" id="editRole" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="admin">Admin</option>
                <option value="pustakawan">Pustakawan</option>
                <option value="anggota">Anggota</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Batal</button>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Simpan</button>
    </div>
</form>

    </div>
</div>

<script>
function openEditModal(user) {
    // Mengisi field dengan data yang ada
    document.getElementById('editName').value = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editKelas').value = user.kelas || '';  // Jika kelas kosong, tetap menampilkan placeholder
    document.getElementById('editRole').value = user.role;
    
    // Menetapkan URL untuk action form ke route yang benar
    document.getElementById('editForm').action = `/admin/update-user/${user.id}`; // Pastikan ini mengarah ke route yang benar di web.php
    
    // Menampilkan modal
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    // Menyembunyikan modal setelah klik batal atau di luar modal
    document.getElementById('editModal').classList.add('hidden');
}

// Menutup modal ketika mengklik di luar area modal
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target.id === 'editModal') {
        closeEditModal();
    }
});
</script>

<style>
    table {
        border-collapse: separate;
        border-spacing: 0;
    }
    th, td {
        border-bottom: 1px solid #e5e7eb;
    }
    tr:last-child td {
        border-bottom: none;
    }
</style>

@endsection
