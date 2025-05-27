<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\SourceDeVerification;
use Core\Repositories\BaseRepository;

class SourceDeVerificationRepository extends BaseRepository
{

   /**
    * SourceDeVerificationRepository constructor.
    *
    * @param SourceDeVerification $sourceDeVerification
    */
   public function __construct(SourceDeVerification $sourceDeVerification)
   {
       parent::__construct($sourceDeVerification);
   }
}
