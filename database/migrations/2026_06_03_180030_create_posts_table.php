<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('company');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#0176D3');
            $table->enum('status', ['planned', 'progress', 'pending', 'published'])->default('planned');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
