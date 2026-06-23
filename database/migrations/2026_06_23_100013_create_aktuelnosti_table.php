<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktuelnosti', function (Blueprint $table) {
            $table->id();
            $table->string('naslov');
            $table->text('sadrzaj');
            $table->date('datum_objave');
            $table->boolean('objavljeno')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktuelnosti');
    }
};
