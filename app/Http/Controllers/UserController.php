<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // 1. Tampilkan Daftar Akun
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    // 2. Form Tambah Akun Baru
    public function create()
    {
        return view('users.create');
    }

    // 3. Simpan Akun Baru ke Database
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,teknisi'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', "Akun untuk {$request->name} berhasil dibuat!");
    }

    // 4. Hapus Akun
    public function destroy(User $user)
    {
        // Cegah admin menghapus akunnya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Kamu tidak bisa menghapus akunmu sendiri!');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun berhasil dihapus permanen.');
    }
}
