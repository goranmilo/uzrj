<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uplate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clanarina_id')->constrained('clanarine')->cascadeOnDelete();
            $table->decimal('iznos', 10, 2);
            $table->date('datum');
            $table->string('nacin')->nullable(); // gotovina, racun, kartica
            $table->string('referenca')->nullable(); // broj uplatnice
            $table->foreignId('evidentirao')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uplate');
    }
};
