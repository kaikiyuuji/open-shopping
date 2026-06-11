<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->cascadeOnDelete();
            $table->date('data');
            $table->decimal('valor_total', 10, 2);
            $table->string('forma_pagamento');
            $table->boolean('parcelado')->default(false);
            $table->unsignedInteger('numero_parcelas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
