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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id('certificationId');
            $table->unsignedBigInteger('userId');
            $table->string('certificationName')->nullable();
            $table->string('issuingOrganization')->nullable();
            $table->date('issueDate')->nullable();
            $table->date('expirationDate')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
