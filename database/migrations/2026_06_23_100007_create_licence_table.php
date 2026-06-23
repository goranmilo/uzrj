<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clanovi')->cascadeOnDelete();
            $table->string('broj');
            $table->date('datum_izdavanja');
            $table->date('datum_isteka');
            $table->enum('status', ['vazeca', 'istice', 'istekla'])->default('vazeca');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licence');
    }
};
