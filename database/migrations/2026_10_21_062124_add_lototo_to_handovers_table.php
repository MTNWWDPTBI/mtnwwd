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
        if (Schema::hasTable('handovers') && ! Schema::hasColumn('handovers', 'lototo_status')) {
            Schema::table('handovers', function (Blueprint $table) {
                $table->enum('lototo_status', ['Sudah', 'Belum'])->default('Belum')->after('problem_description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('handovers') && Schema::hasColumn('handovers', 'lototo_status')) {
            Schema::table('handovers', function (Blueprint $table) {
                $table->dropColumn('lototo_status');
            });
        }
    }
};
