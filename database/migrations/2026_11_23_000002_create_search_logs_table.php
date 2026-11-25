<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();$table->string('normalized_query', 80)->index();
            $table->string('display_query', 80);$table->unsignedInteger('results_count')->default(0);
            $table->string('session_hash', 64)->nullable()->index();$table->timestamps();

            $table->index(['normalized_query', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};