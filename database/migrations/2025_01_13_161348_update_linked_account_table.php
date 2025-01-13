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
        Schema::table('linked_accounts', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');

            $table->unsignedBigInteger('student_id')->required()->after('id');
            $table->unsignedBigInteger('parent_id')->required()->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linked_accounts', function (Blueprint $table) {
            $table->dropColumn('student_id');
            $table->dropColumn('parent_id');

            $table->unsignedBigInteger('student_id')->nullable()->after('id');
            $table->unsignedBigInteger('parent_id')->nullable()->after('student_id');
        });
    }
};
