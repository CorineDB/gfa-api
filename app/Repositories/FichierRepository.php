<?php

namespace App\Repositories;

use App\Models\Fichier;
use Core\Repositories\BaseRepository;

class FichierRepository extends BaseRepository
{

   /**
    * fichierRepository constructor.
    *
    * @param Fichier $fichier
    */
   public function __construct(Fichier $fichier)
   {
       parent::__construct($fichier);
   }
}
