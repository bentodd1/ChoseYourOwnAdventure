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
        Schema::create('adventure_pieces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sessionId');
            $table->index('sessionId');
            $table->text('content');
            $table->string('role');
            $table->integer('order');
            $table->foreign('sessionId')->references('id')->on('adventure_sessions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adventure_pieces');
    }
};
