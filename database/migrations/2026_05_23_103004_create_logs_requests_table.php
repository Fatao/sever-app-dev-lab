<?php

declare(strict_types=1);

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
        Schema::create('logs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('full_url');
            $table->string('method', 10);
            $table->string('controller_path')->nullable()->index();
            $table->string('controller_method')->nullable();
            $table->json('request_body')->nullable();
            $table->json('request_headers')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->smallInteger('response_status');
            $table->json('response_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->timestamp('called_at')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index('response_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_requests');
    }
};