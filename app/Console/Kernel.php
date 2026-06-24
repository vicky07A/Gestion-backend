<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * On enregistre notre commande pour qu'elle soit disponible
     */
    protected $commands = [
        Commands\SendProjectDeadlineReminders::class,
    ];

    /**
     * Le scheduler — définit quand lancer automatiquement nos commandes
     */
    protected function schedule(Schedule $schedule): void
    {
        // Lance la commande tous les jours à 8h00 du matin
        $schedule->command('projects:send-reminders')->dailyAt('08:00');
    }

    protected function bootstrapWith(): array
    {
        return \Illuminate\Foundation\Application::$bootstrappers;
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}