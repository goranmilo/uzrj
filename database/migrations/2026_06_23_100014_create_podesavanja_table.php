<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podesavanja', function (Blueprint $table) {
            $table->id();
            $table->string('kljuc')->unique();
            $table->text('vrednost')->nullable();
            $table->string('tip')->default('string'); // string, integer, boolean, json
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podesavanja');
    }
};
