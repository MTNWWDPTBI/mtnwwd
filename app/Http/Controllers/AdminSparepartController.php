<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\Assembly;
use App\Models\Inventory;
use Illuminate\Http\Request;

class AdminSparepartController extends Controller
{
    public function index(Request $request)
    {
        $spareparts = Sparepart::with(['assemblies.machine'])->paginate(10);
        return view('admin.spareparts.index', compact('spareparts'));
    }

    public function create()
    {
        $assemblies = Assembly::with('machine')->get();
        return view('admin.spareparts.create', compact('assemblies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sap_code' => 'required|unique:spareparts,sap_code',
            'part_name' => 'required',
            'bin_location' => 'required',
            'assembly_id' => 'required'
        ]);

        // Simpan Sparepart
        $sparepart = Sparepart::create([
            'part_id' => $request->part_id ?? ('SP' . (Sparepart::max('id') + 1)),
            'sap_code' => $request->sap_code,
            'part_name' => $request->part_name,
            'bin_location' => $request->bin_location,
        ]);

        // Hubungkan ke Assembly (BOM)
        $sparepart->assemblies()->attach($request->assembly_id, [
            'qty' => $request->qty ?? 1,
            'unit' => $request->unit ?? 'Pcs'
        ]);

        return redirect()->route('spareparts.index')->with('success', 'Master Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        $sparepart->load(['assemblies']);
        $assemblies = Assembly::with('machine')->get();
        return view('admin.spareparts.edit', compact('sparepart', 'assemblies'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $request->validate([
            'sap_code' => 'required|unique:spareparts,sap_code,' . $sparepart->id,
            'part_name' => 'required',
            'bin_location' => 'required',
            'assembly_id' => 'required'
        ]);

        $sparepart->update([
            'sap_code' => $request->sap_code,
            'part_name' => $request->part_name,
            'bin_location' => $request->bin_location,
        ]);

        // Update relasi Assembly
        $sparepart->assemblies()->sync([
            $request->assembly_id => [
                'qty' => $request->qty ?? 1,
                'unit' => $request->unit ?? 'Pcs'
            ]
        ]);

        return redirect()->route('spareparts.index')->with('success', 'Master Sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart)
    {
        if ($sparepart->inventory) {
            $sparepart->inventory->delete();
        }
        $sparepart->assemblies()->detach();
        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('success', 'Master Sparepart berhasil dihapus.');
    }
}