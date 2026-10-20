<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wwd_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name'); 
            $table->text('description')->nullable(); 
            $table->string('pic_leader')->nullable(); 
            $table->date('target_date')->nullable();
            $table->json('column_headers')->nullable(); 
            $table->enum('status', ['OPEN', 'ON PROGRESS', 'CLOSED'])->default('OPEN');
            $table->timestamps();
        });

        Schema::create('wwd_project_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wwd_project_id')->constrained('wwd_projects')->onDelete('cascade');
            $table->foreignId('machine_unit_id')->constrained('machine_units')->onDelete('cascade');
            $table->json('dynamic_data')->nullable(); 
            $table->enum('status', ['OPEN', 'COMPLETION', 'CLOSE'])->default('OPEN');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wwd_project_details');
        Schema::dropIfExists('wwd_projects');
    }
};