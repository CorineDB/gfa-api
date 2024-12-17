<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Jobs\RappelJob;

use App\Models\Rappel;
use App\Notifications\RappelNotification;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RappelCron extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rappel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des alertes pour les rappels';

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
        $rappels = Rappel::where('dateAvant', '<=',  date('Y-m-d h:i:s'))->
                           where('dateAvant', '>=',  date('Y-m-d')." 00:00:00")->
                           get();

        foreach($rappels as $rappel)
        {
            /*$days = "0 days";

            for($i = 0, $frequence = 0; $i < 3; $i++, $frequence++, $days = $frequence." days")
            {

                $carbonDate = strtotime(date('Y-m-d', strtotime($days)));

                if($carbonDate - time() < 0)
                {
                    $carbonDate = time() + 60;
                }

                $this->info($carbonDate);

                RappelJob::dispatch($rappel->user, $rappel->description)->delay($carbonDate - time());
            }*/

            $statut = $rappel->statuts->last();

            if($statut && $statut['etat'] != 1)
            {
                $data['texte'] = $rappel->description;
                $data['id'] = $rappel->id;
                $data['auteurId'] = 0;
                $notification = new RappelNotification($data);

                $rappel->user->notify($notification);

                $notification = $rappel->user->notifications->last();

                event(new NewNotification($this->formatageNotification($notification, $rappel->user)));

                RappelJob::dispatch($rappel->user, $rappel->description);

                //$rappel->statuts()->create(['etat' => 1]);
                $rappel->statut = 1;
                $rappel->save();
            }
        }
    }

}
