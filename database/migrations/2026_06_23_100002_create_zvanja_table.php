<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zvanja', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->boolean('aktivno')->default(true);
            $table->integer('redosled')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zvanja');
    }
};
