<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edukacije', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->text('opis')->nullable();
            $table->dateTime('datum_pocetka');
            $table->dateTime('datum_zavrsetka')->nullable();
            $table->string('lokacija')->nullable();
            $table->integer('kapacitet')->nullable();
            $table->string('predavaci')->nullable();
            $table->string('akreditacioni_broj')->nullable();
            $table->string('vrsta_kme')->nullable();
            $table->decimal('bodovi', 5, 2)->default(0);
            $table->string('ciljna_grupa')->nullable();
            $table->enum('status', ['planirana', 'odrzana', 'otkazana'])->default('planirana');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edukacije');
    }
};
