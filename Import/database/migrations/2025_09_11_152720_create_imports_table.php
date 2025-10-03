<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension', 4);
            $table->string('hash_content', 64)->index();
            $table->json('settings')->nullable();
            $table->tinyInteger('total_iterations')->unsigned()->default(0);
            $table->tinyInteger('confirmed_iterations')->unsigned()->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
