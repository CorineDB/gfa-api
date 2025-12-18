<?php

namespace App\Repositories;

use App\Models\Categorie;
use Core\Repositories\BaseRepository;

class CategorieRepository extends BaseRepository
{

   /**
    * CategorieRepository constructor.
    *
    * @param Categorie $categorie
    */
   public function __construct(Categorie $categorie)
   {
       parent::__construct($categorie);
   }
}