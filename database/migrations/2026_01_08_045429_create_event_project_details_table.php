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
        Schema::create('event_project_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_project_id')->constrained()->onDelete('cascade');
            $table->foreignId('budget_item_id')->constrained();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_project_details');
    }
};
