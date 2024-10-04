<?php

namespace App\Services;

use App\Repositories\IndicateurValueKeyRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurValueKeyServiceInterface;

/**
* Class IndicateurValueKeyService
* @package Core\Services
*/
class IndicateurValueKeyService extends BaseService implements IndicateurValueKeyServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * IndicateurValueKeyRepository constructor.
     *
     * @param IndicateurValueKeyRepository $indicateurValueKeyRepository
     */
    public function __construct(IndicateurValueKeyRepository $indicateurValueKeyRepository)
    {
        parent::__construct($indicateurValueKeyRepository);
    }

}