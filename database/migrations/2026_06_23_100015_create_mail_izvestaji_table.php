<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_izvestaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clan_id')->constrained('clanovi')->cascadeOnDelete();
            $table->string('tip'); // mesecni_izvestaj, podsetnik_clanarina, podsetnik_edukacija, podsetnik_bodovi
            $table->timestamp('poslat_at');
            $table->string('status')->default('poslat'); // poslat, greska
            $table->text('greska')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_izvestaji');
    }
};
