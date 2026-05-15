<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('note_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('note_labels', function (Blueprint $table) {
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->primary(['note_id', 'label_id']);
        });

        Schema::create('note_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_with_id')->constrained('users')->cascadeOnDelete();
            $table->enum('permission', ['read', 'edit'])->default('read');
            $table->timestamp('shared_at')->useCurrent();
            $table->unique(['note_id', 'shared_with_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('note_shares');
        Schema::dropIfExists('note_labels');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('note_images');
    }
};
