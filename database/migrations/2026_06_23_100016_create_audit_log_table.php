<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('akcija'); // create, update, delete
            $table->string('entitet'); // npr. clan, edukacija, clanarina
            $table->unsignedBigInteger('entitet_id')->nullable();
            $table->json('pre')->nullable();
            $table->json('posle')->nullable();
            $table->timestamp('vreme');
            $table->string('ip_adresa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
