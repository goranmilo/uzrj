<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clanovi', function (Blueprint $table) {
            $table->id();
            $table->string('ime');
            $table->string('prezime');
            $table->string('jmbg', 13)->unique();
            $table->string('okg')->nullable(); // broj komore
            $table->string('email')->unique()->nullable();
            $table->string('telefon')->nullable();
            $table->foreignId('sprema_id')->nullable()->constrained('spreme')->nullOnDelete();
            $table->foreignId('zvanje_id')->nullable()->constrained('zvanja')->nullOnDelete();
            $table->foreignId('odeljenje_id')->nullable()->constrained('odeljenja')->nullOnDelete();
            $table->foreignId('kategorija_clanarine_id')->nullable()->constrained('clanarina_kategorije')->nullOnDelete();
            $table->enum('status', ['aktivan', 'neaktivan', 'suspendovan'])->default('aktivan');
            $table->date('datum_uclanjenja')->nullable();
            $table->string('clanski_broj')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clanovi');
    }
};
