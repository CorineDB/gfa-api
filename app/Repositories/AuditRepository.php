<?php

namespace App\Repositories;

use App\Models\Audit;
use Core\Repositories\BaseRepository;

class AuditRepository extends BaseRepository
{

   /**
    * auditRepository constructor.
    *
    * @param Audit $audit
    */
   public function __construct(Audit $audit)
   {
       parent::__construct($audit);
   }
}
