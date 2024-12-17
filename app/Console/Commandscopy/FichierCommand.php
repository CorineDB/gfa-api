<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\Fichier;

class FichierCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fichiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $fichiers = Fichier::all();

        foreach($fichiers as $fichier)
        {
            $tab = explode('/', $fichier->chemin);
            
            if($tab[0] != 'upload')
            {
                $fichier->chemin = 'upload/'.$fichier->chemin;
                $fichier->save();
            }
        }
    }
}
