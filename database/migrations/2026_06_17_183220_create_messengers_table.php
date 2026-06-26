<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messengers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('environment', ['local', 'dev', 'prod']);
            $table->string('token_env_var');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messengers');
    }
};
