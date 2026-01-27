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
        Schema::create('field_guard_rules', function (Blueprint $table) {
            $table->id();
            $table->string('model_class')->index();
            $table->string('field_name');
            $table->json('read_policy')->nullable();
            $table->json('write_policy')->nullable();
            $table->string('mask')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['model_class', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_guard_rules');
    }
};
