<?php

namespace App\Services;

use App\Repositories\NouvelleProprieteRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\NouvelleProprieteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface NouvelleProprieteServiceInterface
* @package Core\Services\Interfaces
*/
class NouvelleProprieteService extends BaseService implements NouvelleProprieteServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * NouvelleProprieteService constructor.
     *
     * @param NouvelleProprieteRepository $nouvelleProprieteRepository
     */
    public function __construct(NouvelleProprieteRepository $nouvelleProprieteRepository)
    {
        parent::__construct($nouvelleProprieteRepository);
        $this->repository = $nouvelleProprieteRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $nouvellePropriete = $this->repository->create($attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $nouvellePropriete, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $nouvellePropriete = $this->repository->update($id, $attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $nouvellePropriete, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
