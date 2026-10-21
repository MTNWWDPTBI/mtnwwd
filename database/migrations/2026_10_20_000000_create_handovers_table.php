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
        if (! Schema::hasTable('handovers')) {
            Schema::create('handovers', function (Blueprint $table) {
                $table->id();
                $table->date('handover_date');
                $table->string('shift');
                $table->string('team')->nullable();
                $table->string('machine_name');
                $table->text('problem_description');
                $table->string('status')->default('OPEN');
                $table->string('reported_by')->nullable();
                $table->string('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};
