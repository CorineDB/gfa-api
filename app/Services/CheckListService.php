<?php

namespace App\Services;

use App\Repositories\CheckListRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\CheckListServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface CheckListServiceInterface
* @package Core\Services\Interfaces
*/
class CheckListService extends BaseService implements CheckListServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CheckListRepository constructor.
     *
     * @param CheckListRepository $checkListComRepository
     */
    public function __construct(CheckListRepository $checkListComRepository)
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

            $checkList = $this->repository->fill($attributs);

            $checkList->save();

            if(array_key_exists('commentaire', $attributs))
            {
                $commentaire = ['contenu' => $attributs['commentaire']];
                $commentaires = $checkList->commentaires()->create($commentaire);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($checkList));

            //LogActivity::addToLog("Enregistrement", $message, get_class($checkList), $checkList->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Check list agence communication ou ong crée", 'data' => $checkList, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($checkList, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($checkList))
            {
                $checkList = $this->repository->findById($checkList);
            }
            else{
                $checkList = $checkList;
            }

            $checkList = $this->repository->fill($attributs);

            if(array_key_exists('commentaire', $attributs))
            {
                $commentaire = $checkList->commentaires->last();
                $commentaire->contenu = $attributs['commentaire'];
                $commentaire->save();
            }

            $checkList->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($checkList));

            //LogActivity::addToLog("Modification", $message, get_class($checkList), $checkList->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée  modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
