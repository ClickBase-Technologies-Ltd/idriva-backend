<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->nullable();
            $table->string('type'); // follow, post_like, post_comment, post_share, job_post, job_application, message, system
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like sender_id, post_id, job_id, etc.
            $table->timestamp('readAt')->nullable();
            $table->timestamps();
            
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};