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
       Schema::table('users', function (Blueprint $table) {
    $table->string('full_name');
    $table->integer('age')->nullable();
    $table->string('gender')->nullable();
    $table->string('country')->nullable();
    $table->string('school')->nullable();
    $table->string('grade')->nullable();
    $table->json('preferred_exams')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
