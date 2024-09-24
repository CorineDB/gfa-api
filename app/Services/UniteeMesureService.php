<?php

namespace App\Services;

use App\Repositories\UniteeMesureRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\UniteeMesureServiceInterface;

/**
* Interface UniteeMesureServiceInterface
* @package Core\Services\Interfaces
*/
class UniteeMesureService extends BaseService implements UniteeMesureServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * UniteeMesureRepository constructor.
     *
     * @param UniteeMesureRepository $uniteeRepository
     */
    public function __construct(UniteeMesureRepository $uniteeRepository)
    {
        parent::__construct($uniteeRepository);
    }

}