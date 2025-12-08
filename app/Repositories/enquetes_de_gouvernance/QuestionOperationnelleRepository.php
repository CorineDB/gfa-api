<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
use Core\Repositories\BaseRepository;

class QuestionOperationnelleRepository extends BaseRepository
{
   /**
    * QuestionOperationnelleRepository constructor.
    *
    * @param QuestionOperationnelle $questionOperationnelle
    */
   public function __construct(QuestionOperationnelle $questionOperationnelle)
   {
       parent::__construct($questionOperationnelle);
   }
}
