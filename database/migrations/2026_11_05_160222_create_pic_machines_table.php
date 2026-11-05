<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pic_machines', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke ID akun user teknisi
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Kode unik mesin SAP (contoh: KA-WWD-NDB-3006 atau KA-WWD-SHX-4016)
            $table->string('functional_location');
            $table->timestamps();

            // Mencegah duplikasi mesin yang sama untuk user yang sama
            $table->unique(['user_id', 'functional_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pic_machines');
    }
};
