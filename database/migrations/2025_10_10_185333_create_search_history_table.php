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
        Schema::create('search_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prospect_id')->nullable()->constrained('prospects')->onDelete('set null');
            $table->text('query_text');
            $table->json('extracted_criteria');
            $table->unsignedInteger('results_count');
            $table->decimal('top_match_score', 5, 2);
            $table->timestamp('created_at')->useCurrent();
            // Laravel no manejará updated_at para esta tabla, ya que los registros son inmutables.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_history');
    }
};
