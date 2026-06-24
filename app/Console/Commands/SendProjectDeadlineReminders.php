<?php

namespace App\Console\Commands;

use App\Mail\ProjectDeadlineReminder;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProjectDeadlineReminders extends Command
{
    /**
     * Le nom de la commande — utilisé pour la lancer manuellement
     * php artisan projects:send-reminders
     */
    protected $signature = 'projects:send-reminders';

    /**
     * Description de la commande
     */
    protected $description = 'Envoie des rappels par mail pour les projets dont la deadline approche';

    public function handle()
    {
        // On récupère la date d'aujourd'hui et dans 3 jours
        $today    = now()->startOfDay();
        $in3Days  = now()->addDays(3)->endOfDay();

        // On cherche tous les projets dont la deadline est entre aujourd'hui et dans 3 jours
        $projects = Project::with('user') // On charge l'utilisateur lié au projet
            ->whereBetween('deadline', [$today, $in3Days])
            ->get();

        // Si aucun projet trouvé, on affiche un message et on arrête
        if ($projects->isEmpty()) {
            $this->info('Aucun projet avec une deadline proche.');
            return;
        }

        // Pour chaque projet trouvé, on envoie un email au propriétaire
        foreach ($projects as $project) {
            Mail::to($project->user->email)
                ->send(new ProjectDeadlineReminder($project->user, $project));

            // On affiche un message dans le terminal pour chaque email envoyé
            $this->info("Email envoyé à {$project->user->email} pour le projet : {$project->name}");
        }

        $this->info("✅ {$projects->count()} rappel(s) envoyé(s) avec succès !");
    }
}