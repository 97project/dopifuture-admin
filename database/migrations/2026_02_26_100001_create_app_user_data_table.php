<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_user_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('connector_type')->nullable()->comment('MissionWay|WayStartup|Vega');
            $table->string('external_user_id')->nullable()->comment('ID in external system');
            $table->json('external_data')->nullable()->comment('Full raw connector response');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'application_id']);
            $table->index('connector_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_data');
    }
};
