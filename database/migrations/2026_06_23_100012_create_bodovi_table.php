<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bodovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clanovi')->cascadeOnDelete();
            $table->foreignId('edukacija_id')->nullable()->constrained('edukacije')->nullOnDelete();
            $table->decimal('bodovi', 5, 2);
            $table->integer('licencna_godina');
            $table->date('datum');
            $table->string('razlog')->nullable(); // za korekcije
            $table->foreignId('evidentirao')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bodovi');
    }
};
