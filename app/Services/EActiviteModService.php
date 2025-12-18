<?php

namespace App\Services;

use App\Repositories\EActiviteModRepository;
use App\Repositories\BailleurRepository;
use App\Repositories\SiteRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\ModRepository;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EActiviteModServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface EActiviteModServiceInterface
* @package Core\Services\Interfaces
*/
class EActiviteModService extends BaseService implements EActiviteModServiceInterface
{

    use Pta;
    /**
     * @var service
     */
    protected $repository, $bailleurReposotory, $siteRepository, $modRepository, $programmeRepository;

    /**
     * ActiviteService constructor.
     *
     * @param EActiviteModRepository $eActiviteMod
     */
    public function __construct(EActiviteModRepository $eActiviteMod,
                                ProgrammeRepository $programmeRepository,
                                BailleurRepository $bailleurRepository,
                                SiteRepository $siteRepository,
                                ModRepository $modRepository)
    {
        parent::__construct($eActiviteMod);
        $this->repository = $eActiviteMod;
        $this->bailleurRepository = $bailleurRepository;
        $this->siteRepository = $siteRepository;
        $this->modRepository = $modRepository;
        $this->programmeRepository = $programmeRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            if(!($site = $this->siteRepository->findById($attributs['siteId']))) throw new Exception( "Ce site n'existe pas", 500);
            if(!($bailleur = $this->bailleurRepository->findById($attributs['bailleurId']))) throw new Exception( "Ce bailleur n'existe pas", 500);
            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);
            if(!($mod = $this->modRepository->findById($attributs['modId']))) throw new Exception( "Ce mod n'existe pas", 500);


            $attributs = array_merge($attributs, ['siteId' => $site->id]);
            $attributs = array_merge($attributs, ['bailleurId' => $bailleur->id]);
            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
            $attributs = array_merge($attributs, ['modId' => $mod->id]);
            $activite = $this->repository->fill($attributs);
            $activite->save();

            $statut = ['etat' => $attributs['statut']];
            $statuts = $activite->statuts()->create($statut);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un  " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Enregistrement", $message, get_class($activite), $activite->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $activite, 'statut de l\'activité' => $statuts->etat, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($activiteId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();


        try
        {
            if(!($site = $this->siteRepository->findById($attributs['siteId']))) throw new Exception( "Ce site n'existe pas", 500);
            if(!($bailleur = $this->bailleurRepository->findById($attributs['bailleurId']))) throw new Exception( "Ce bailleur n'existe pas", 500);
            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);
            if(!($mod = $this->modRepository->findById($attributs['modId']))) throw new Exception( "Ce mod n'existe pas", 500);


            $attributs = array_merge($attributs, ['siteId' => $site->id]);
            $attributs = array_merge($attributs, ['bailleurId' => $bailleur->id]);
            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
            $attributs = array_merge($attributs, ['modId' => $mod->id]);
            $activite = $this->repository->findById($activiteId);
            $activite = $activite->fill($attributs);
            $activite->save();

            $a = $this->repository->findById($activiteId);
            $statut = $a->statuts->last();

            $this->verifieStatut($statut->etat, $attributs['statut']);

            $statut = ['etat' => $attributs['statut']];
            $statuts = $a->statuts()->create($statut);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Modification", $message, get_class($activite), $activite->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $activite, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
