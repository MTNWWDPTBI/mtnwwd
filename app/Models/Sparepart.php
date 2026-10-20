<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = ['part_id', 'sap_code', 'part_name', 'bin_location'];

    // Relasi ke Assembly melalui tabel pivot assembly_spareparts (BOM)
    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_spareparts', 'sparepart_id', 'assembly_id')
            ->withPivot('qty', 'unit', 'description');
    }

    // Relasi ke stok TPM (Satu sparepart bisa ada di banyak lokasi TPM)
    public function tpmStocks()
    {
        return $this->hasMany(TpmStock::class, 'sparepart_id');
    }

    // Relasi ke riwayat transaksi TPM (Satu sparepart punya banyak history)
    public function tpmTransactions()
    {
        return $this->hasMany(TpmTransaction::class, 'sparepart_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}
