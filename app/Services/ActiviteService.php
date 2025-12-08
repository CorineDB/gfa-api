<?php

namespace App\Services;

use App\Repositories\ActiviteRepository;
use App\Repositories\UserRepository;
use App\Repositories\ComposanteRepository;
use App\Repositories\DureeRepository;
use App\Models\Tache;
use App\Models\Activite;
use App\Http\Resources\ActiviteResource;
use App\Http\Resources\plans\PlansDecaissementResource;
use App\Http\Resources\SuiviFinancierResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Http\Resources\TacheResource;
use App\Jobs\GenererPta;
use App\Models\Composante;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\UniteeDeGestion;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ActiviteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Interface ActiviteServiceInterface
 * @package Core\Services\Interfaces
 */
class ActiviteService extends BaseService implements ActiviteServiceInterface
{

    use Pta;
    /**
     * @var service
     */
    protected $repository, $composanteRepository, $userRepository, $dureeRepository;

    /**
     * ActiviteService constructor.
     *
     * @param ActiviteRepository $activiteRepository
     */
    public function __construct(ActiviteRepository $activiteRepository,
        ComposanteRepository $composanteRepository,
        UserRepository $userRepository,
        DureeRepository $dureeRepository)
    {
        parent::__construct($activiteRepository);
        $this->repository = $activiteRepository;
        $this->composanteRepository = $composanteRepository;
        $this->userRepository = $userRepository;
        $this->dureeRepository = $dureeRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            $activites = [];

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $activites = Auth::user()->profilable->projet->activites;
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $activites = Auth::user()->programme->activites;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActiviteResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filtre(array $attributs = ['*']): JsonResponse
    {
        try {
            $activites = [];
            $controle = 0;

            foreach (Activite::all()->where('statut', '>=', 0) as $activite) {
                $controle = 0;

                $durees = $activite->durees;

                foreach ($durees as $duree) {
                    $debutTab = explode('-', $duree->debut);
                    $finTab = explode('-', $duree->fin);

                    if ($debutTab[0] == $attributs['annee'] || $finTab[0] == $attributs['annee']) {
                        $controle = 1;
                        array_push($activites, $activite);
                        break;
                    }
                   //return response()->json( ['debut'=>$debutTab[0],'equiv'=>$debutTab[0] == $attributs['annee'], 'statut' =>    $controle, 'message' => null, 'data' =>   $activites , 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);


                   // if($controle) array_push($activites, $activite);

                }
            }
          //  return response()->json(['statut' => 'success', 'message' => null, 'data' => $activites, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);


            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActiviteResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filterActivities(array $attributs): JsonResponse{

        try {
            $activites = [];

            if(isset($attributs['statut'])){
                $activites = $this->repository->where("statut", $attributs['statut'])->get();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActiviteResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function plansDeDecaissement($id): JsonResponse
    {

        try {

            if (!($activite = $this->repository->findById($id)))
                throw new Exception("Ce act$activite n'existe pas", 500);

            $plansDeDecaissement = $activite->planDeDecaissements;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PlansDecaissementResource::collection($plansDeDecaissement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($activiteId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($activite = $this->repository->findById($activiteId))){
                throw new Exception("Cette activite n'existe pas", 500);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($activite->suivis->sortByDesc("created_at")), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivisFinancier($activiteId, array $attributs): JsonResponse
    {
        try {
            if (!($activite = $this->repository->findById($activiteId))){
                throw new Exception("Cette activite n'existe pas", 500);
            }

            $suiviFinanciers = $activite->suiviFinanciers($attributs["annee"] ?? null)
                ->when(isset($attributs["trimestre"]) && $attributs["trimestre"], function($query) use ($attributs) {
                    return $query->where("trimestre", $attributs['trimestre']);
                })
                ->sortByDesc("created_at");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuiviFinancierResource::collection($suiviFinanciers), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $composante = $this->composanteRepository->findById($attributs['composanteId']);
            //$this->userRepository->findById($attributs['userId']);
            //$this->userRepository->findById($attributs['structureResponsableId']);
            //$this->userRepository->findById($attributs['structureAssocieId']);

            if ($composante->projet->debut > $attributs['debut'])
                throw new Exception("La date de début de l'activité est antérieur à celui du projet", 500);

            if ($composante->projet->fin < $attributs['fin'])
                throw new Exception("La date de fin de l'activité est supérieur à celui du projet", 500);

            $attributs = array_merge($attributs, ['statut' => -1, 'programmeId' => auth()->user()->programmeId, 'position' => $this->position($composante, 'activites')]);

            $activite = $this->repository->fill($attributs);

            $activite->save();

            $activite = $activite->fresh();

            /*$statut = ['etat' => -2];

            $activite->statuts()->create($statut);*/

            $duree = ['debut' => $attributs['debut'], 'fin' => $attributs['fin']];

            $activite->durees()->create($duree);

            //$activite->structures()->attach($attributs['structureResponsableId'], ['type' => 'Responsable']);

            //$activite->structures()->attach($attributs['structureAssocieId'], ['type' => 'Associée']);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a enregistré un " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Enregistrement", $message, get_class($activite), $activite->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ActiviteResource($activite), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($activiteId, array $attribut = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            $activite = $this->repository->findById($activiteId);

            if (isset($activite)) {
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new ActiviteResource($activite), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            } else
                throw new Exception("Cette activité n'existe pas", 400);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($activiteId, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (array_key_exists('position', $attributs))
                unset($attributs['position']);

            if ((!is_object($activiteId)))
                $activite = $this->repository->findById($activiteId);
            else {
                $activite = $activiteId;
            }

            if (array_key_exists('statut', $attributs) && $attributs['statut'] === -1) {

                if (!Auth::user()->hasPermissionTo('validation'))
                    throw new Exception("Vous n'avez pas la permission de faire la validation", 500);

                $parentStatut = $activite->composante->statut;

                if ($parentStatut < -1) {
                    throw new Exception("La composante de cette activité n'est pas enocre validé", 500);
                }

                $last = $activite->statut;

                $this->verifieStatut($last, $attributs['statut']);

                if ($last === -2 && $attributs['statut'] !== -2) {
                    $attributs = array_merge($attributs, ['position' => $this->position($activite->composante, 'activites')]);

                    //$activite->save();
                }
            }

            $activite = $activite->fill($attributs);

            $activite->save();

            if (array_key_exists('structureResponsableId', $attributs)) {
                $activite->structures()->attach($attributs['structureResponsableId'], ['type' => 'Responsable']);

                if (!$activite->structureResponsable()) {

                    $activite->structures()->attach($attributs['structureResponsableId'], ['type' => 'Responsable']);
                } else {
                    $activite->structures()->wherePivot('type', 'Responsable')->sync([$attributs['structureResponsableId'] => ['type' => 'Responsable']]);
                }

            }

            if (array_key_exists('structureAssocieId', $attributs)) {
                if (!$activite->structureAssociee()) {

                    $activite->structures()->attach($attributs['structureAssocieId'], ['type' => 'Associée']);
                } else {
                    $activite->structures()->wherePivot('type', 'Associée')->sync([$attributs['structureAssocieId'] => ['type' => 'Associée']]);
                }

            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Suppression", $message, get_class($activite), $activite->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ActiviteResource($activite), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function taches($id): JsonResponse
    {

        try {
            $taches = [];

            if ($id !== null && $id !== 'undefined')
                $activite = $this->repository->findById($id); //Retourner les données du premier activite
            else
                $activite = $this->repository->firstItem(); //Retourner les données du premier activite

            if ($activite)
                $taches = $this->triPta($activite->taches);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => TacheResource::collection($taches), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function stats($activiteId): JsonResponse
    {
        try {
            if (!($activite = $this->repository->findById($activiteId))){
                throw new Exception("Cette activite n'existe pas", 500);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($activite->suivis->sortByDesc("created_at")), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeStatut($activiteId, $attributs): JsonResponse
    {
        try
        {
            $activite = $this->repository->findById($activiteId);

            if($activite)
            {
                foreach ($activite->taches as $key => $tache) {
                    if(isset($attributs['statut']) && $attributs['statut'] == 2){
                        $tache->suivis()->create(['poidsActuel'=> 100]);
                        $tache->statut = 2;
                        $tache->save();
                    }

                    else if(isset($attributs['statut']) && $attributs['statut'] == -1){

                        $tache->suivis()->create(['poidsActuel'=> 0]);
                        $tache->statut = -1;
                        $tache->save();
                    }

                    else if(isset($attributs['statut']) && $attributs['statut'] == 0){

                        $tache->suivis()->create(['poidsActuel'=> 0]);
                        $tache->statut = 0;
                        $tache->save();
                    }
                }

                if(isset($attributs['statut']) && $attributs['statut'] == 2){
                    $activite->suivis()->create(['poidsActuel'=> 100]);
                }
                else if(isset($attributs['statut']) && $attributs['statut'] == -1){
                    $activite->suivis()->create(['poidsActuel'=> 0]);
                }
                else if(isset($attributs['statut']) && $attributs['statut'] == 0){
                    $activite->suivis()->create(['poidsActuel'=> 0]);
                }

                $activite->statut = $attributs['statut'];
                $activite->save();
                GenererPta::dispatch(Auth::user()->programme)->delay(5);
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new ActiviteResource($activite), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else throw new Exception("Cette activite n'existe pas", 400);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ajouterDuree(array $attributs, $id): JsonResponse
    {

        DB::beginTransaction();

        try {
            $activite = $this->repository->findById($id);

            if ($activite->composante->projet->debut > $attributs['debut'])
                throw new Exception("La date de début de l'activité est antérieur à celui du projet", 500);
            if ($activite->composante->projet->fin < $attributs['fin'])
                throw new Exception("La date de fin de l'activité est supérieur à celui du projet", 500);

            $duree = $activite->durees->last();
            if (isset($duree)) {
                if (!($this->verifieDuree($duree->toArray(), $attributs)))
                    throw new Exception("Durée antérieur à ". $duree->debut . " - " . $duree->fin, 500);
            }

            $duree = $activite->durees()->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a enregistré un " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Enregistrement", $message, get_class($duree), $duree->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function modifierDuree(array $attributs, $activiteId, $dureeId): JsonResponse
    {
        DB::beginTransaction();

        try {
            $duree = $this->dureeRepository->findById($dureeId);

            //$activite = $this->repository->findById($attributs['activiteId']);*/

            $activite = $this->repository->findById($activiteId);

            $duree = $activite->durees()->where('id', $duree->id)->first();

            if ($activite->composante->projet->debut > $attributs['debut'])
                throw new Exception("La date de début de l'activité est antérieur à celui du projet", 500);
            if ($activite->composante->projet->fin < $attributs['fin'])
                throw new Exception("La date de fin de l'activité est supérieur à celui du projet", 500);

            if($activite->durees->count() > 1){
                foreach ($activite->durees as $key => $index) {
                    if ($index->id === $duree->id) {
                        if($key != 0 && $key != ($activite->durees->count()-1)){
                            if($this->verifieDuree(($activite->durees[$key-1])->toArray(), $attributs)){
                                throw new Exception("Durée antérieur à celle qui était la", 500);
                            }
                            if($attributs['fin'] >= $activite->durees[$key+1]['debut']) {
                                throw new Exception("Durée superieur à celle qui suit", 500);
                            }
                        }
                        else if($key === 0){
                            if($attributs['fin'] >= $activite->durees[$key+1]['debut']) {
                                throw new Exception("Durée superieur à celle qui suit", 500);
                            }
                        }
                        else if($key === $activite->durees->count()){
                            if($this->verifieDuree(($activite->durees[$key-2])->toArray(), $attributs)){
                                throw new Exception("Durée antérieur à celle qui était la", 500);
                            }
                        }
                    }
                }
            }

            /*$duree = $activite->durees->last();
            if (isset($duree)) {
                if (!($this->verifieDuree($duree->toArray(), $attributs)))
                    throw new Exception("Durée antérieur à celle qui était la", 500);
            }*/

            $duree->debut = $attributs['debut'];
            $duree->fin = $attributs['fin'];
            $duree->save();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié une " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Modification", $message, get_class($duree), $duree->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deplacer(array $attributs, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $activite = $this->repository->findById($id);

            if ($attributs['toPermute']) {
                $secondeActivite = Activite::where('id', $attributs['activiteId'])->get();

                if ($activite->composante->id != $secondeActivite->composante->id)
                    throw new Exception("Les deux activité n'appartiennent pas à la même composante", 500);

                $temp = $activite->position;
                $activite->position = $secondeActivite->position;
                $secondeActivite->position = $temp;

                $activite->save();
                $secondeActivite->save();
            } else {
                if ($activite->position < $attributs['position']) {

                    $activites = Activite::where('composanteId', $activite->composante->id)->
                        where('position', '<=', $attributs['position'])->
                        where('position', '>', $activite->position)->
                        get();

                    if (count($activites)) {
                        foreach ($activites as $a) {
                            $a->position--;
                            $a->save();
                        }
                    }

                } else {
                    $activites = Activite::where('composanteId', $activite->composante->id)->
                        where('position', '>=', $attributs['position'])->
                        where('position', '<', $activite->position)->
                        get();

                    if (count($activites)) {
                        foreach ($activites as $a) {
                            $a->position++;
                            $a->save();
                        }
                    }

                }

                $activite->position = $attributs['position'];
                $activite->save();

            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " deplacé une " . strtolower(class_basename($activite));

            //LogActivity::addToLog("Suppression", $message, get_class($activite), $activite->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => 'Deplacement effectué', 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ppm(array $attributs): JsonResponse
    {
        try {
            if (array_key_exists('composanteId', $attributs)) {
                $composante = Composante::find($attributs['composanteId']);
                $ppm = $composante->ppm();
            }

            if (array_key_exists('projetId', $attributs)) {
                $projet = Projet::find($attributs['projetId']);
                $ppm = $projet->ppm();
            }

            if (array_key_exists('programmeId', $attributs)) {
                $programme = Programme::find($attributs['programmeId']);
                $ppm = $programme->ppm();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActiviteResource::collection($ppm), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
