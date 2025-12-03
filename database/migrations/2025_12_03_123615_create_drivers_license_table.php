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
        Schema::create('drivers_license', function (Blueprint $table) {
            $table->id();
            $table->string('licenseId')->nullable();
            $table->unsignedBigInteger('userId');
            $table->string('licenseNumber')->nullable();
            $table->date('issueDate')->nullable();
            $table->date('expiryDate')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers_license');
    }
};
