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
        Schema::create('booked_times', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tutor_id')->constrained()->onDelete('cascade');
            $table->bigInteger('student_id')->constrained()->onDelete('cascade');
            $table->string('day');
            $table->string('time_slot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booked_times');
    }
};
