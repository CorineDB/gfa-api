<?php

namespace App\Services;

use App\Http\Resources\SitesResource;
use App\Models\BailleurSite;
use App\Models\EntrepriseExecutant;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\UniteeDeGestion;
use App\Repositories\SiteRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SiteServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface SiteServiceInterface
* @package Core\Services\Interfaces
*/
class SiteService extends BaseService implements SiteServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SiteRepository constructor.
     *
     * @param SiteRepository $siteRepository
     */
    public function __construct(SiteRepository $siteRepository)
    {
        parent::__construct($siteRepository);
        $this->repository = $siteRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $sites = [];
            
            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $sites = Auth::user()->profilable->projet->sites;
            } 
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $sites = Auth::user()->programme->sites;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SitesResource::collection($sites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

            $entreprises = [];
            $user = Auth::user();
            $programme = Programme::find($user->programmeId);

            $attributs = array_merge($attributs, ['nom' => ucfirst(strtolower($attributs['nom']))]);

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id]);

            //if((BailleurSite::where('bailleurId', $attributs['bailleurId'])->where('programmeId', $attributs['programmeId'])->first())) throw new Exception( "Le bailleur a deja un site dans ce programme", 500);

            /*foreach($attributs['entrepriseExecutantId'] as $id)
            {
                $entreprise = EntrepriseExecutant::findByKey($id);
                if($programme->id != $entreprise->user->programmeId) throw new Exception( "Cet entreprise n'est pas dans le programme", 500);
                array_push($entreprises, $entreprise->id);
            }*/

            //$attributs = array_merge($attributs, ['entrepriseExecutantId' => $entreprises]);

            $site = $this->repository->fill($attributs);

            $site->save();

            $site->refresh();

            if(isset($attributs['projetId'])){
                $site->projets()->attach($attributs['projetId'], ["programmeId" => $attributs['programmeId']]);
            }

            if(isset($attributs['indicateurId'])){
                $site->indicateurs()->attach($attributs['indicateurId'], ["programmeId" => $attributs['programmeId']]);
            }

            /*$site->bailleurs()->attach($attributs['bailleurId'], ["programmeId" => $attributs['programmeId']]);

            $site->entreprises()->attach($attributs['entrepriseExecutantId'], ["programmeId" => $attributs['programmeId']]);*/

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($site));

            //LogActivity::addToLog("Enregistrement", $message, get_class($site), $site->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Site crée", 'data' => new SitesResource($site), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($site, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($site))
            {
                $site = $this->repository->findById($site);
            }
            else{
                $site = $site;
            }

            $attributs = array_merge($attributs, ['nom' => ucfirst(strtolower($attributs['nom']))]);

            $site = $site->fill($attributs);

            /*if($bailleur = $site->bailleurs()->wherePivot("programmeId", $attributs['programmeId']))
            {
                $bailleur->sync($attributs['bailleurId']);
            }*/

            $site->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($site));

            //LogActivity::addToLog("Modification", $message, get_class($site), $site->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée du site modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
