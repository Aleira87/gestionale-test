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
        Schema::table('users', function (Blueprint $table) {
            //aggiungiamo quindi il campo company_id
            $table->foreignId('company_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // qui invece faremo la modifica inversa, ossia se abbiamo aggiunto una constrained foreign key la dobbiamo togliere
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
