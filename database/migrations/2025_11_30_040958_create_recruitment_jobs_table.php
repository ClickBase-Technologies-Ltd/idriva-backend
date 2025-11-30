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
        Schema::create('recruitment_jobs', function (Blueprint $table) {
            $table->id('jobId');
            $table->unsignedBigInteger('companyId')->nullable();
            $table->string('jobTitle')->nullable();
            $table->text('jobDescription')->nullable();
            $table->string('jobLocation')->nullable();
            $table->string('jobType')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('applicationDeadline')->nullable();
            $table->enum('jobStatus', ['open', 'closed'])->default('open');
            $table->string('jobImage')->nullable();
            $table->unsignedBigInteger('postedBy')->nullable();
            $table->foreign('companyId')->references('companyId')->on('companies')->onDelete('set null');
            $table->foreign('postedBy')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_jobs');
    }
};
