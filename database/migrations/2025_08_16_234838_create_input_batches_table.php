<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('input_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('input_id')->constrained('input')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->decimal('quantity_total', 10, 3); //almacena la cantidad inicial del insumo
            $table->decimal('quantity_remaining', 10, 3); // lamacena la cantidad del inumo y su conversion a g
            $table->decimal('unit_price', 10, 3); // almacena el precio unitario del inusmo
            $table->decimal('subtotal_price', 10, 3); // almacena el susbtotal unit_price * quantity_total
            $table->integer('batch_number'); // Almacena el numero del lote
            $table->string('original_unit', 30); // almacena g
            $table->date('received_date')->useCurrent(); // Almacena la fecha recivida del insumo
            $table->timestamps();

            $table->index(['input_id', 'received_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('input_batches');
    }
};
