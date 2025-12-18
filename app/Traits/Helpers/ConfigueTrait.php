<?php


namespace App\Traits\Helpers;


trait ConfigueTrait{

    protected $dureeValiditerLien = 1440; // en minute

    protected $periodeValiditerMotDePasse = 30; // en jours
    protected $sms_api_account_id = "accfrk"; // Compte SMS API Username
    protected $sms_api_account_password = "Tr9zEuv9"; // Compte SMS API Password
    protected $sms_api_key = "YWNjZnJrOlRyOXpFdXY5"; //API KEY (à utiliser dans les requêtes de l'API)
    protected $sms_api_url = "https://api.e-mc.co/v3"; // URL pour l'API (à utiliser dans les requêtes de l'API)

}
