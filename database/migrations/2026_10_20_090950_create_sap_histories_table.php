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
        Schema::create('sap_histories', function (Blueprint $table) {
            $table->id();

            // Waktu & Periode
            $table->string('month', 20)->nullable();
            $table->string('week', 20)->nullable();
            $table->date('date')->nullable(); // Kolom 'Day' di Excel

            // Kategorisasi
            $table->string('cat', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('plant', 50)->nullable();
            $table->string('area', 100)->nullable();
            $table->string('dept', 100)->nullable();

            // Identitas Mesin & Order
            $table->string('functional_location')->nullable();
            $table->string('indicator', 50)->nullable();
            $table->string('maint_order')->nullable(); // Menggunakan string untuk menghindari error format angka besar
            $table->text('maint_order_desc')->nullable();

            // Detail Kerusakan
            $table->string('code_cause')->nullable();
            $table->string('error_code')->nullable();
            $table->string('code_group')->nullable();
            $table->string('object_part')->nullable();
            $table->string('problem')->nullable();

            // PIC & Status
            $table->string('creator')->nullable();
            $table->string('executor')->nullable();
            $table->string('activity')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('confirmation_text')->nullable();

            // Metrik Angka / Jam
            $table->integer('notfis')->nullable();
            $table->decimal('response_hrs', 8, 2)->nullable();
            $table->decimal('actual_hrs', 8, 2)->nullable();
            $table->decimal('bm_hrs', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_histories');
    }
};
