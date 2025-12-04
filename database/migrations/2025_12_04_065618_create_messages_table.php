<?php
// database/migrations/[timestamp]_create_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Changed to match your User model primary key convention
            $table->unsignedBigInteger('senderId');
            $table->unsignedBigInteger('receiverId');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('senderId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiverId')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['senderId', 'receiverId']);
            $table->index(['receiverId', 'is_read']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};