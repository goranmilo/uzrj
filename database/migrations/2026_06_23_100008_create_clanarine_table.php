<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clanarine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clanovi')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('clanarina_periodi')->cascadeOnDelete();
            $table->foreignId('kategorija_id')->constrained('clanarina_kategorije')->cascadeOnDelete();
            $table->decimal('iznos_zaduzenja', 10, 2);
            $table->decimal('iznos_placen', 10, 2)->default(0);
            $table->enum('status', ['placeno', 'delimicno', 'dug'])->default('dug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clanarine');
    }
};
