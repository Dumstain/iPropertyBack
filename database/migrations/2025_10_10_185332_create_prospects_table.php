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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('added_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['activo', 'contactado', 'en_negociacion', 'cerrado', 'descartado'])->default('activo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
