<?php

namespace App\Console\Commands;

use App\Models\Categorie;
use App\Models\Indicateur;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;

class IndicateurCommand extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:indicateur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'indicateur non classé ';

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
        $categorie = Categorie::where('nom', 'Non classée')->first();

        $indicateurs = Indicateur::where('categorieId', 0)->get();

        foreach($indicateurs as $indicateur)
        {
            $indicateur->categorieId = $categorie->id;
            $indicateur->save();
        }
    }

}
