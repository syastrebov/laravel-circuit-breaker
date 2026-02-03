<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('circuit_breaker', function (Blueprint $table) {
            $table->string('prefix')->nullable(false);
            $table->string('name')->nullable(false);
            $table
                ->enum('state', ['closed', 'half_open', 'open'])
                ->nullable(false)
                ->default('closed');
            $table->integer('state_timestamp')->nullable();
            $table->integer('failed_attempts')->nullable(false)->default(0);
            $table->integer('half_open_attempts')->nullable(false)->default(0);
            $table->primary(['prefix', 'name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('circuit_breaker');
    }
};
