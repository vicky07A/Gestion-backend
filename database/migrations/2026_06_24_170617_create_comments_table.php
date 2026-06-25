<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des commentaires
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Lié à une tâche — si la tâche est supprimée, les commentaires aussi
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            // Lié à un utilisateur
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Le contenu du commentaire
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Supprime la table des commentaires
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};