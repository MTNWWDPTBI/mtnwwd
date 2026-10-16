<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Machine;
use Illuminate\Http\Request;

class AssemblyController extends Controller
{
    public function index()
    {
        // Memuat data assembly beserta nama mesin induknya
        $assemblies = Assembly::with('machine')->orderBy('machine_id')->paginate(10);
        return view('assemblies.index', compact('assemblies'));
    }

    public function create()
    {
        $machines = Machine::orderBy('machine_name')->get();
        return view('assemblies.create', compact('machines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'assembly_code' => 'required|unique:assemblies,assembly_code',
            'assembly_name' => 'required'
        ]);

        Assembly::create($request->all());
        return redirect()->route('assemblies.index')->with('success', 'Assembly berhasil ditambahkan.');
    }

    public function edit(Assembly $assembly)
    {
        $machines = Machine::orderBy('machine_name')->get();
        return view('assemblies.edit', compact('assembly', 'machines'));
    }

    public function update(Request $request, Assembly $assembly)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'assembly_code' => 'required|unique:assemblies,assembly_code,' . $assembly->id,
            'assembly_name' => 'required'
        ]);

        $assembly->update($request->all());
        return redirect()->route('assemblies.index')->with('success', 'Data assembly berhasil diperbarui.');
    }

    public function destroy(Assembly $assembly)
    {
        try {
            $assembly->delete();
            return redirect()->route('assemblies.index')->with('success', 'Assembly berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('assemblies.index')->withErrors(['error' => 'Gagal menghapus! Assembly ini masih memiliki Sparepart yang terhubung.']);
        }
    }
}