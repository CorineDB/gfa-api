<?php

namespace App\Services;

use App\Repositories\ProprieteRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ProprieteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface ProprieteServiceInterface
* @package Core\Services\Interfaces
*/
class ProprieteService extends BaseService implements ProprieteServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * ProprieteService constructor.
     *
     * @param ProprieteRepository $proprieteRepository
     */
    public function __construct(ProprieteRepository $proprieteRepository)
    {
        parent::__construct($proprieteRepository);
        $this->repository = $proprieteRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $propriete = $this->repository->create($attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $propriete, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $propriete = $this->repository->update($id, $attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $propriete, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
