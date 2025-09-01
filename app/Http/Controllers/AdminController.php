<?php

// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // Cek apakah user memiliki akses admin
    private function checkAdminAccess()
    {
        $user = Auth::user();
        if (!$user || ($user->role !== 'admin' && $user->role !== 'pustakawan')) {
            abort(403, 'Anda tidak memiliki akses ke halaman admin.');
        }
    }

    // Menampilkan semua user
    public function index()
    {
        $this->checkAdminAccess();
        $users = User::all();
        return view('admin.index', compact('users'));
    }

    // Update role user
    public function updateRole(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'role' => 'required|in:admin,pustakawan,anggota',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.index')->with('success', 'Role user berhasil diperbarui.');
    }

    // Update data user (termasuk kelas)
    public function updateUser(Request $request, $id)
    {
        $this->checkAdminAccess();
        
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'kelas' => 'nullable|string|max:50',
            'role' => 'required|in:admin,pustakawan,anggota',
        ]);

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'kelas' => $request->kelas,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Hapus user
    public function destroy($id)
    {
        $this->checkAdminAccess();
        
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.index')->with('success', 'User berhasil dihapus.');
    }
}
