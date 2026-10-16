<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('spareparts', function (Blueprint $table) {
        $table->id();
        $table->string('part_id')->unique(); // Untuk SP0001
        $table->string('sap_code')->nullable(); // Untuk 2559056
        $table->string('part_name');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
