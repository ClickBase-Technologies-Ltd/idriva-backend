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
        Schema::create('work_experience', function (Blueprint $table) {
            $table->id('workExperienceId');
            $table->unsignedBigInteger('userId');
            $table->string('companyName')->nullable();
            $table->string('position')->nullable();
            $table->string('location')->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isCurrentlyWorking')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experience');
    }
};
