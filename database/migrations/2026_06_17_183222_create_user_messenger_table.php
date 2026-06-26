<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_messenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('messenger_id')->constrained()->onDelete('cascade');
            $table->string('messenger_user_id');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'messenger_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_messenger');
    }
};
