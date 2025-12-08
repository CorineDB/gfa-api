<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Jobs\ChangementStatutJob;
use App\Jobs\SendEmailJob;
use App\Models\Activite;
use App\Models\Ano;
use App\Models\Tache;
use App\Models\User;
use App\Notifications\ChangementStatutNotification;
use App\Notifications\RappelAnoNotification;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;

class RappelAno extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rappel-ano';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rappel ano';

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
        $anos = Ano::all();

        foreach($anos as $ano)
        {

            if($ano->durees->last()->fin == date('Y-m-d') && $ano->statut == 0  && $ano->commentaires->count() <= 1)
            {
                $data['texte'] = "Veillez repondre a l'ano : \"".$ano->dossier."\" sur votre dashboard";
                $data['id'] = $ano->id;
                $data['auteurId'] = 0;
                $notification = new RappelAnoNotification($data);

                $ano->bailleur->user->notify($notification);

                $notification = $ano->bailleur->user->notifications->last();

                event(new NewNotification($this->formatageNotification($notification, $ano->bailleur->user)));

                dispatch(new SendEmailJob($ano->bailleur->user, "rappel-ano", "Rappel de traitement de la demande d'ano {$ano->dossier}"))->delay(now()->addSeconds(15));
            }

        }
    }

}
