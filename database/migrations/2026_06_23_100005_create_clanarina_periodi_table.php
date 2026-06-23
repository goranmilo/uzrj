<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clanarina_periodi', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->enum('vrsta', ['godisnje', 'mesecno', 'kvartalno'])->default('godisnje');
            $table->date('vazi_od');
            $table->date('vazi_do');
            $table->boolean('aktivan')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clanarina_periodi');
    }
};
