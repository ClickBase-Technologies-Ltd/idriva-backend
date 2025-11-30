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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('companyId');
            $table->string('companyName')->nullable();
            $table->string('companyAddress')->nullable();
            $table->string('companyEmail')->nullable();
            $table->string('companyPhone')->nullable();
            $table->string('companyWebsite')->nullable();
            $table->text('companyDescription')->nullable();
            $table->string('companyLogo')->nullable();
            $table->string('companyIndustry')->nullable();
            $table->string('companySize')->nullable();
            $table->string('companyLocation')->nullable();
            $table->integer('companyFoundedYear')->nullable();
            $table->enum('companyStatus', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
