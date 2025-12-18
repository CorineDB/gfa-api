<?php

namespace App\Services;

use App\Http\Resources\EActiviteResource;
use App\Models\EActivite;
use App\Models\EntrepriseExecutant;
use App\Models\Programme;
use App\Repositories\EActiviteRepository;
use App\Repositories\DureeRepository;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EActiviteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface EActiviteServiceInterface
* @package Core\Services\Interfaces
*/
class EActiviteService extends BaseService implements EActiviteServiceInterface
{

    use Pta;

    /**
     * @var service
     */
    protected $repository;

    /**
     * ActiviteService constructor.
     *
     * @param EActiviteRepository $activiteRepository
     */
    public function __construct(EActiviteRepository $activiteRepository,
                                DureeRepository $dureeRepository)
    {
        parent::__construct($activiteRepository);
        $this->repository = $activiteRepository;
        $this->dureeRepository = $dureeRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $user = Auth::user();
            $programme = $user->programme;

            $eActivites = EActivite::where('programmeId', $programme->id)->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => EActiviteResource::collection($eActivites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $entreprises = [];
            $user = Auth::user();
            $programme = Programme::find($user->programmeId);
            if($programme->debut > $attributs['debut']) throw new Exception( "La date de début de l'activité est antérieur à celui du programme", 500);
            if($programme->fin < $attributs['fin']) throw new Exception( "La date de fin de l'activité est supérieur à celui du programme", 500);

            $position = EActivite::where('programmeId', $programme->id)->count() + 1;
            $code = $programme->code.'.'.$position;
            $attributs = array_merge($attributs, ['code' => $code, 'programmeId'=> $programme->id]);

            $eActivite = $this->repository->fill($attributs);
            $eActivite->save();

            foreach($attributs['entrepriseExecutantId'] as $id)
            {
                $entreprise = EntrepriseExecutant::findByKey($id);
                if($programme->id != $entreprise->user->programmeId) throw new Exception( "Cet entreprise n'est pas dans le programme", 500);
                array_push($entreprises, $entreprise->id);

                $statut = ['etat' => $attributs['statut'], 'entrepriseId' => $entreprise->id];
                $eActivite->statuts()->create($statut);
            }

            $duree = ['debut' => $attributs['debut'], 'fin' => $attributs['fin']];
            $durees = $eActivite->durees()->create($duree);

            $eActivite->entrepriseExecutants()->attach($entreprises);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($eActivite));

            //LogActivity::addToLog("Enregistrement", $message, get_class($eActivite), $eActivite->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $eActivite, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            $eActivite = $this->repository->findById($id);
            $oldDuree = $eActivite->duree;
            $programme = $eActivite->programme;

            if(array_key_exists('debut', $attributs))
            {
                if($programme->debut > $attributs['debut']) throw new Exception( "La date de début de l'activité est antérieur à celui du programme", 500);

                if($oldDuree->fin < $attributs['debut']) throw new Exception( "La date de début de l'activité est supérieur à la date de fin", 500);

                $oldDuree->debut = $attributs['debut'];
                $oldDuree->save();
            }

            if(array_key_exists('fin', $attributs))
            {
                if($programme->fin < $attributs['fin']) throw new Exception( "La date de fin de l'activité est supérieur à celui du programme", 500);

                if($oldDuree->debut > $attributs['fin']) throw new Exception( "La date de fin de l'activité est antérieur à la date de debut", 500);

                $oldDuree->fin = $attributs['fin'];
                $oldDuree->save();
            }

            if(array_key_exists('entrepriseExecutantId', $attributs)) unset($attributs['entrepriseExecutantId']);

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'code' => $eActivite->code]);
            $eActivite = $eActivite->fill($attributs);
            $eActivite->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($eActivite));

            //LogActivity::addToLog("Modification", $message, get_class($eActivite), $eActivite->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $eActivite, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ajouterDuree(array $attributs, $id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($eActivite = $this->repository->findById($id))) throw new Exception( "Cette activité n'existe pas", 500);
            if($eActivite->programme->debut > $attributs['debut']) throw new Exception( "La date de début de l'activité est antérieur à celui du programme", 500);
            if($eActivite->programme->fin < $attributs['fin']) throw new Exception( "La date de fin de l'activité est supérieur à celui du programme", 500);

            $duree = $eActivite->durees->last();
            if(isset($duree))
            {
                if(!($this->verifieDuree($duree->all(), $attributs))) throw new Exception( "Durée antérieur à celle qui était la", 500);
            }

            $duree = $eActivite->durees()->create($attributs);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function modifierDuree(array $attributs, $id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($duree = $this->dureeRepository->findById($id))) throw new Exception( "Cette durée n'existe pas", 500);

            if(!($eActivite = $this->repository->findById($attributs['activiteId']))) throw new Exception( "Cette activité n'existe pas", 500);
            if($eActivite->programme->debut > $attributs['debut']) throw new Exception( "La date de début de l'activité est antérieur à celui du projet", 500);
            if($eActivite->programme->fin < $attributs['fin']) throw new Exception( "La date de fin de l'activité est supérieur à celui du projet", 500);

            $duree = $eActivite->durees->last();
            if(isset($duree))
            {
                if(!($this->verifieDuree($duree->all(), $attributs))) throw new Exception( "Durée antérieur à celle qui était la", 500);
            }

            $duree->debut = $attributs['debut'];
            $duree->fin = $attributs['fin'];
            $duree->save();

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
