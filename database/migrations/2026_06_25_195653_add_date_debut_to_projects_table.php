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
        Schema::table('projects', function (Blueprint $table) {
            // On ajoute le champ date_debut juste après la description (ou ailleurs) et il peut être vide (nullable)
            $table->date('date_debut')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // En cas de retour en arrière, on supprime la colonne
            $table->dropColumn('date_debut');
        });
    }
};