<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->string('mode', 10); // 'time' | 'cycles'
            $table->integer('minutes')->nullable();
            $table->integer('cycles')->nullable();
            $table->integer('cycle_minutes')->nullable();
            $table->float('kwh')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
