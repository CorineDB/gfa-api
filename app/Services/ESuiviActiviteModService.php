<?php

namespace App\Services;

use App\Repositories\ESuiviActiviteModRepository;
use App\Repositories\EActiviteModRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ESuiviActiviteModServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/**
* Interface ESuiviActiviteModServiceInterface
* @package Core\Services\Interfaces
*/
class ESuiviActiviteModService extends BaseService implements ESuiviActiviteModServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $eActiviteModReposotory;

    /**
     * ActiviteService constructor.
     *
     * @param ESuiviActiviteModRepository $eSuiviActiviteMod
     */
    public function __construct(ESuiviActiviteModRepository $eSuiviActiviteMod,
                                EActiviteModRepository $eActiviteMod)
    {
        parent::__construct($eSuiviActiviteMod);
        $this->repository = $eSuiviActiviteMod;
        $this->eActiviteModReposotory = $eActiviteMod;

    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($eActiviteMod = $this->eActiviteModRepository->findById($attributs['eActiviteModId']))) throw new Exception( "Cette activite n'existe pas", 500);

            $attributs = array_merge($attributs, ['eActiviteModId' => $eActiviteMod->id]);

            $suivi = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($suivi));

            //LogActivity::addToLog("Enregistrement", $message, get_class($suivi), $suivi->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivi, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            if(!($eActiviteMod = $this->eActiviteModRepository->findById($attributs['eActiviteModId']))) throw new Exception( "Cette activite n'existe pas", 500);

            $attributs = array_merge($attributs, ['eActiviteModId' => $eActiviteMod->id]);

            $suivi = $this->repository->update($id, $attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($suivi));

            //LogActivity::addToLog("Modification", $message, get_class($suivi), $suivi->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivi, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
