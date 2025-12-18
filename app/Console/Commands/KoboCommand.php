<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\File;


class KoboCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kobo';

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
        $client = new Client();
        $url = "https://kf.kobotoolbox.org/api/v2/assets";

        if (!file_exists(storage_path('app')."/kobo"))
            {
                //mkdir (".".Storage::url('app')."/kobo", 0777);
                File::makeDirectory(storage_path('app').'/kobo',0777,true);

            }

            $file = "";
            $filename = "/kobo/kobo.json";
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file);

        $json = [];

        do {
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Token 3d4b08315551155a0e8cbccc395bc54ea77a97d5",
                    'Conten-Type' => "application/json",
                    "Accept" => "application/json"
                ],
            ]);

            $responseJson = json_decode($response->getBody()->getContents());

            foreach($responseJson->results as $result)
            {
                array_push($json, $result);
            }

            $url = $responseJson->next;



        } while ($url);

        $file = json_encode($json);
        $filename = "/kobo/kobo.json";
        $path = storage_path('app').$filename;
        $bytes = file_put_contents($path, $file);

    }

}
