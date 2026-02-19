<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20)->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_token')->nullable();
            $table->string('app_version', 20)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
