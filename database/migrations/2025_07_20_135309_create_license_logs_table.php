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
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 32);
            $table->string('domain', 128);
            $table->text('user_agent')->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->boolean('is_valid')->default(false);
            $table->string('ip_address', 45)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_logs');
    }
};
