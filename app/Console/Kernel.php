<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckAlertes::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Vérifier les alertes chaque jour à 8:00
        $schedule->command('alertes:check')->dailyAt('08:00');
        
        // Nettoyer les vieilles alertes chaque semaine
        $schedule->command('alertes:clean')->weekly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}