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
        if (! Schema::hasTable('tpm_transactions')) {
            Schema::create('tpm_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tpm_part_id')->constrained('tpm_parts')->cascadeOnDelete();
                $table->string('transaction_type');
                $table->integer('qty');
                $table->string('operator_name')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpm_transactions');
    }
};
