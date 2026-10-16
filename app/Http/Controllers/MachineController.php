<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::orderBy('machine_code', 'asc')->paginate(10);
        return view('machines.index', compact('machines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_code' => 'required|unique:machines,machine_code',
            'machine_name' => 'required'
        ]);

        Machine::create($request->all());
        return redirect()->route('machines.index')->with('success', 'Mesin berhasil ditambahkan.');
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'machine_code' => 'required|unique:machines,machine_code,' . $machine->id,
            'machine_name' => 'required'
        ]);

        $machine->update($request->all());
        return redirect()->route('machines.index')->with('success', 'Data mesin berhasil diperbarui.');
    }

    public function destroy(Machine $machine)
    {
        try {
            $machine->delete();
            return redirect()->route('machines.index')->with('success', 'Mesin berhasil dihapus.');
        } catch (\Exception $e) {
            // Error ini akan muncul jika mesin sedang dipakai di tabel Assembly/Sparepart
            return redirect()->route('machines.index')->withErrors(['error' => 'Gagal menghapus! Mesin ini masih memiliki data Assembly/Sparepart yang terhubung.']);
        }
    }
}