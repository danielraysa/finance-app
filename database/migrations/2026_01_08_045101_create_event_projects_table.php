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
        Schema::create('event_projects', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->string('status')->default('planned');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->date('verified_date')->nullable();
            $table->date('complete_date')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('cash_flow_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_projects');
    }
};
