<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Donner le rôle super_admin à un utilisateur via son email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$email}");
            return;
        }

        $user->role = 1;
        $user->save();

        $this->info("L'utilisateur {$user->name} ({$email}) est maintenant Super Administrateur.");
    }
}
