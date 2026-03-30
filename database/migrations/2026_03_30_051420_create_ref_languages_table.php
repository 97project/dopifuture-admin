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
        Schema::create('ref_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('ISO 639-1 language code (tr, en, de, fr, etc.)');
            $table->string('name', 100);
            $table->boolean('is_default')->default(false)->comment('Default language flag');
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_languages');
    }
};
