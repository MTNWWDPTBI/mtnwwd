<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Sparepart;

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil semua data mesin untuk ditampilkan di "Mesin Populer"
        $machines = Machine::all();
        
        // Menghitung total sparepart (untuk contoh kategori di dashboard)
        $totalSpareparts = Sparepart::count();

        return view('dashboard', compact('machines', 'totalSpareparts'));
    }
}