<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prisustva', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edukacija_id')->constrained('edukacije')->cascadeOnDelete();
            $table->foreignId('clan_id')->constrained('clanovi')->cascadeOnDelete();
            $table->boolean('prijavljen')->default(false);
            $table->boolean('prisutan')->default(false);
            $table->string('qr_token')->unique()->nullable();
            $table->timestamp('qr_poslat_at')->nullable();
            $table->timestamp('vreme_cekiranja')->nullable();
            $table->decimal('dodeljeni_bodovi', 5, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['edukacija_id', 'clan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prisustva');
    }
};
