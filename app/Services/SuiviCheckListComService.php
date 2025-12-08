<?php

namespace App\Services;

use App\Repositories\SuiviCheckListComRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SuiviCheckListComServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface SuiviCheckListComServiceInterface
* @package Core\Services\Interfaces
*/
class SuiviCheckListComService extends BaseService implements SuiviCheckListComServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $userRepository;

    /**
     * SuiviCheckListComRepository constructor.
     *
     * @param SuiviCheckListComRepository $suiviFinancierMOD
     */
    public function __construct(SuiviCheckListComRepository $suiviFinancierMOD)
    {
        parent::__construct($suiviFinancierMOD);
        $this->repository = $suiviFinancierMOD;
    }



    /**
     * Création de site
     *
     *
     */
    public function create($attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            $suiviFinancierMOD = $this->repository->fill($attributs);

            $suiviFinancierMOD->save();

            if(isset($attributs['commentaire']))
            {
                $suiviFinancierMOD->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suiviFinancierMOD, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($suiviFinancierMOD, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($suiviFinancierMOD))
            {
                $suiviFinancierMOD = $this->repository->findById($suiviFinancierMOD);
            }
            else{
                $suiviFinancierMOD = $suiviFinancierMOD;
            }

            $suiviFinancierMOD = $suiviFinancierMOD->fill($attributs);

            $suiviFinancierMOD->save();

            if(isset($attributs['commentaire']))
            {
                $suiviFinancierMOD->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée du site modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
