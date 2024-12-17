<?php

namespace App\Console\Commands;

use App\Models\Bailleur;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UniteeDeGestion;
use App\Models\User;
use Illuminate\Console\Command;

class BailleurUserCommande extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bailleur-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $uniteeDeGestion = User::where('type', 'unitee-de-gestion')->first();

        $users = User::where('profilable_type', get_class(new Bailleur()))->where('type', '!=', 'bailleur')->get();

        foreach($users as $user)
        {
            $user->profilable_type = get_class(new UniteeDeGestion());
            $user->profilable_id = $uniteeDeGestion->id;
            $user->save();
        }
    }

}
