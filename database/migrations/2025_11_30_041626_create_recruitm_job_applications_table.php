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
        Schema::create('recruitment_job_applications', function (Blueprint $table) {
            $table->id('applicationId');
            $table->unsignedBigInteger('jobId')->nullable();
            $table->unsignedBigInteger('applicantId')->nullable();
            $table->date('applicationDate')->nullable();
            $table->string('applicationStatus')->nullable();
            $table->text('coverLetter')->nullable();
            $table->string('resumePath')->nullable();
            $table->foreign('jobId')->references('jobId')->on('recruitment_jobs')->onDelete('set null');
            $table->foreign('applicantId')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_job_applications');
    }
};
