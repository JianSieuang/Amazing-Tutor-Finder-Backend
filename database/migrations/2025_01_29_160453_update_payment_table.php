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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->unsignedBigInteger('parent_user_id')->after('id')->nullable();
            $table->unsignedBigInteger('student_user_id')->after('parent_user_id');

            $table->enum('paid_by', ['parent', 'student'])->after('booked_time_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->dropColumn('parent_user_id');
            $table->dropColumn('student_user_id');
            $table->dropColumn('paid_by');
        });
    }
};
