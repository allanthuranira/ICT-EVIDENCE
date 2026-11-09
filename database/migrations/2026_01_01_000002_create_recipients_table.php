<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->enum('role', ['representative', 'ict_head'])->default('representative');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['campus_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipients');
    }
};
