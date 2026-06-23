<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clanarina_kategorije', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->decimal('iznos', 10, 2);
            $table->boolean('aktivno')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clanarina_kategorije');
    }
};
