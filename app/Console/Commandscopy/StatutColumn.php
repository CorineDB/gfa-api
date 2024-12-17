<?php

namespace App\Console\Commands;

use App\Models\Activite;
use App\Models\Ano;
use App\Models\ArchiveActivite;
use App\Models\ArchiveComposante;
use App\Models\ArchiveProjet;
use App\Models\ArchiveTache;
use App\Models\Composante;
use App\Models\Projet;
use App\Models\Rappel;
use App\Models\Tache;
use Illuminate\Console\Command;

class StatutColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:statut';

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
        foreach(Activite::all() as $activite)
        {
            $activite->statut = $activite->status;
            $activite->save();
        }

        foreach(Ano::all() as $ano)
        {
            $ano->statut = $ano->status;
            $ano->save();
        }

        foreach(ArchiveActivite::all() as $activite)
        {
            $activite->statut = $activite->status;
            $activite->save();
        }

        foreach(ArchiveComposante::all() as $composante)
        {
            $composante->statut = $composante->status;
            $composante->save();
        }

        foreach(ArchiveProjet::all() as $projet)
        {
            $projet->statut = $projet->status;
            $projet->save();
        }

        foreach(ArchiveTache::all() as $tache)
        {
            $tache->statut = $tache->status;
            $tache->save();
        }

        foreach(Composante::all() as $composante)
        {
            $composante->statut = $composante->status;
            $composante->save();
        }

        foreach(Projet::all() as $projet)
        {
            $projet->statut = $projet->status;
            $projet->save();
        }

        foreach(Rappel::all() as $rappel)
        {
            $rappel->statut = $rappel->status;
            $rappel->save();
        }

        foreach(Tache::all() as $tache)
        {
            $tache->statut = $tache->status;
            $tache->save();
        }
    }
}
