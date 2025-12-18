<?php

namespace App\Services;

use App\Repositories\TypeAnoRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\TypeAnoServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface TypeAnoServiceInterface
* @package Core\Services\Interfaces
*/
class TypeAnoService extends BaseService implements TypeAnoServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * TypeAnoService constructor.
     *
     * @param TypeAnoRepository $payeRepository
     */
    public function __construct(TypeAnoRepository $payeRepository)
    {
        parent::__construct($payeRepository);
        $this->repository = $payeRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $typeAno = $this->repository->create($attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $typeAno, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $typeAno = $this->repository->update($id, $attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $typeAno, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
