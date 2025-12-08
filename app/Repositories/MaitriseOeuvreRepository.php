<?php

namespace App\Repositories;

use App\Models\MaitriseOeuvre;
use Core\Repositories\BaseRepository;

class MaitriseOeuvreRepository extends BaseRepository
{

   /**
    * MaitriseOeuvreRepository constructor.
    *
    * @param MaitriseOeuvre $maitriseOeuvre
    */
   public function __construct(MaitriseOeuvre $maitriseOeuvre)
   {
       parent::__construct($maitriseOeuvre);
   }
}