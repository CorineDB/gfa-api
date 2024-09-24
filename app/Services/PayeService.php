<?php

namespace App\Services;

use App\Repositories\PayeRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PayeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface PayeServiceInterface
* @package Core\Services\Interfaces
*/
class PayeService extends BaseService implements PayeServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * PayeService constructor.
     *
     * @param PayeRepository $payeRepository
     */
    public function __construct(PayeRepository $payeRepository)
    {
        parent::__construct($payeRepository);
        $this->repository = $payeRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $paye = $this->repository->create($attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $paye, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $paye = $this->repository->update($id, $attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $paye, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
