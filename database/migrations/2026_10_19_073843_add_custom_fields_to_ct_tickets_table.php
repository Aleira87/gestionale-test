
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ct_tickets', function (Blueprint $table) {

            // Campi di testo
            $table->string('primario')->nullable();
            $table->string('secondario')->nullable();

            // Relazione con il fornitore
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->restrictOnDelete();

            // Relazione con il tecnico
            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('technicians')
                ->restrictOnDelete();

            // Date del ticket
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('on_site_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            // Unicità della combinazione Primario + Secondario
            $table->unique(
                ['primario', 'secondario'],
                'ct_tickets_primario_secondario_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ct_tickets', function (Blueprint $table) {

            $table->dropUnique('ct_tickets_primario_secondario_unique');

            $table->dropConstrainedForeignId('technician_id');
            $table->dropConstrainedForeignId('supplier_id');

            $table->dropColumn([
                'primario',
                'secondario',
                'opened_at',
                'on_site_at',
                'closed_at',
            ]);
        });
    }
};
