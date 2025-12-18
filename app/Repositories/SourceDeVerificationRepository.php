<?php

namespace App\Repositories;

use App\Models\SourceDeVerification;
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
