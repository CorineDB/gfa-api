<?php

namespace App\Services;

use App\Repositories\CheckListComRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\CheckListComServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface CheckListComServiceInterface
* @package Core\Services\Interfaces
*/
class CheckListComService extends BaseService implements CheckListComServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CheckListComRepository constructor.
     *
     * @param CheckListComRepository $checkListComRepository
     */
    public function __construct(CheckListComRepository $checkListComRepository)
    {
        parent::__construct($checkListComRepository);
        $this->repository = $checkListComRepository;
    }


    
    /**
     * Création d'une check list com
     * 
     * 
     */
    public function create($attributs) : JsonResponse
    {
        
        DB::beginTransaction();

        try {
                                    
            $checkListCom = $this->repository->fill(array_merge($attributs, ['ongComId' => Auth::user()->ongCom->id]));

            $checkListCom->save();
            
            DB::commit();
            
            return response()->json(['statut' => 'success', 'message' => "Check list agence communication ou ong crée", 'data' => $checkListCom, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($checkListCom, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($checkListCom))
            {
                $checkListCom = $this->repository->findById($checkListCom);
            }
            else{
                $checkListCom = $checkListCom;
            }

            $checkListCom = $this->repository->fill(array_merge($attributs, ['ongComId' => Auth::user()->ongCom->id]));

            $checkListCom->save();

            DB::commit();
            
            return response()->json(['statut' => 'success', 'message' => "Donnée du agence communication ou ong modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}