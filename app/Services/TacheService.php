<?php

namespace App\Services;

use App\Http\Resources\suivis\SuivisResource;
use App\Repositories\TacheRepository;
use App\Repositories\ActiviteRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\TacheServiceInterface;
use App\Models\Tache;
use App\Http\Resources\TacheResource;
use App\Jobs\GenererPta;
use App\Repositories\DureeRepository;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface UserServiceInterface
* @package Core\Services\Interfaces
*/
class TacheService extends BaseService implements TacheServiceInterface
{

    use Pta;
    /**
     * @var service
     */
    protected $repository, $activiteRepository;

    /**
     * ProjetService constructor.
     *
     * @param TacheRepository $tacheRepository
     */
    public function __construct(TacheRepository $tacheRepository,
                                ActiviteRepository $activiteRepository)
    {
        parent::__construct($tacheRepository);
        $this->repository = $tacheRepository;
        $this->activiteRepository = $activiteRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            return response()->json(['statut' => 'success', 'message' => null, 'data' => TacheResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($tacheId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
           if( !($tache = $this->repository->findById($tacheId)) )  throw new Exception( "Cette tache n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($tache->suivis->sortByDesc("created_at")), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            if(!($activite = $this->activiteRepository->findById($attributs['activiteId']))) throw new Exception( "Cette activité n'existe pas", 500);

            //$activiteduree = $activite->durees->last();

            if(!(app(Tache::class)->verifiePlageDuree($attributs['debut'], $attributs['fin'], $activite))) throw new Exception( "La duree de la tache doit-etre comprise entre la plage de duree d'une activite", 500);


            /*if($activiteduree == null) throw new Exception( "L'activté n'a pas de durée en cours, veillez verifier cela", 500);

            if($activiteduree->debut > $attributs['debut']) throw new Exception( "La date de début de la tache est antérieur à celui de l'activite", 500);

            if($activiteduree->fin < $attributs['fin']) throw new Exception( "La date de fin de la tache est supérieur à celui de l'activite", 500);*/

            $attributs = array_merge($attributs, ['activiteId' => $activite->id, 'programmeId' => auth()->user()->programmeId, 'position' => $this->position($activite, 'taches')]);

            $attributs = array_merge($attributs, ['statut' => -1]);

            $tache = $this->repository->create($attributs);

            $tache = $tache->fresh();

            /*$statut = ['etat' => -2];

            $tache->statuts()->create($statut);*/

            /*

            else
            {
                $attributs = array_merge($attributs, ['position' => $this->position($activite, 'taches')]);
            } */

            $duree = ['debut' => $attributs['debut'], 'fin' => $attributs['fin']];

            $tache->durees()->create($duree);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($tache));

            //LogActivity::addToLog("Enregistrement", $message, get_class($tache), $tache->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new TacheResource($tache), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($tacheId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            if((!is_object($tacheId )))
                $tache = $this->repository->findById($tacheId);
            else {
                $tache = $tacheId;
            }

            if(array_key_exists('statut', $attributs) && $attributs['statut'] === -1 ){

                if(!Auth::user()->hasPermissionTo('validation')) throw new Exception( "Vous n'avez pas la permission de faire la validation", 500);

                $parentStatut = $tache->activite->statut;

                if($parentStatut < -1)
                {
                    throw new Exception( "L'activité de cette tache n'est pas enocre validé", 500);
                }

                $last = $tache->statut;

                $this->verifieStatut($last, $attributs['statut']);

                /*$statut = ['etat' => $attributs['statut']];

                $tache->statuts()->create($statut);*/

                if($last === -2 && $attributs['statut'] !== -2 )
                {
                    $attributs = array_merge($attributs, ['position' => $this->position($tache->activite, 'taches')]);

                    //$tache->save();
                }
            }


            if(array_key_exists('debut', $attributs) || array_key_exists('fin', $attributs) ){
                if(!array_key_exists('debut', $attributs)){
                    $attributs['debut'] = $tache->debut;
                }

                if(!array_key_exists('fin', $attributs)){
                    $attributs['fin'] = $tache->fin;
                }

                $tache->durees()->create($attributs);
            }

            $tache = $tache->fill($attributs);

            $tache->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($tache));

            //LogActivity::addToLog("Modification", $message, get_class($tache), $tache->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new TacheResource($tache), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($tacheId, array $attribut = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            $tache = $this->repository->findById($tacheId);

            if(isset($tache))
            {
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new TacheResource($tache), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else throw new Exception("Cette tache n'existe pas", 400);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeStatut($tacheId): JsonResponse
    {
        try
        {
            $tache = $this->repository->findById($tacheId);

            if(isset($tache))
            {
                $suivi = $tache->suivis()->create(['poidsActuel'=> 0]);
                $tache->statut = -1;
                $tache->save();
                GenererPta::dispatch(Auth::user()->programme)->delay(5);
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new TacheResource($tache), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else throw new Exception("Cette tache n'existe pas", 400);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ajouterDuree(array $attributs, $id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($tache = $this->repository->findById($id))) throw new Exception( "Cette tache n'existe pas", 500);

            if(!($tache->verifiePlageDuree($attributs['debut'], $attributs['fin']))) throw new Exception( "La duree de la tache doit-etre comprise entre la plage de duree d'une activite", 500);

            //if($activiteduree->debut > $attributs['debut']) throw new Exception( "La date de début de la tache est antérieur à celui de l'activite", 500);
            //if($activiteduree->fin < $attributs['fin']) throw new Exception( "La date de fin de la tache est supérieur à celui de l'activite", 500);

            $duree = $tache->durees->last();
            if(isset($duree))
            {
                if(!($this->verifieDuree($duree->toArray(), $attributs))) throw new Exception( "Durée antérieur à celle qui était la", 500);
            }

            $duree = $tache->durees()->create($attributs);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function modifierDuree(array $attributs, $tacheId, $dureeId) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($duree = app(DureeRepository::class)->findById($dureeId))) throw new Exception( "Cette durée n'existe pas", 500);

            if(!($tache = $this->repository->findById($tacheId))) throw new Exception( "Cette tache n'existe pas", 500);

            if(!($duree = $tache->durees()->where('id', $duree->id)->first())) throw new Exception( "Cette duree n'est pas celle de la tache", 500);

            /*if($activiteduree->debut > $attributs['debut']) throw new Exception( "La date de début de la tache est antérieur à celui de l'activite", 500);
            if($activiteduree->fin < $attributs['fin']) throw new Exception( "La date de fin de la tache est supérieur à celui de l'activite", 500);*/

            //dd($tache->activite->durees);
            if(!($tache->verifiePlageDuree($attributs['debut'], $attributs['fin']))) throw new Exception( "La duree de la tache doit-etre comprise entre la plage de duree d'une activite", 500);

            /*$duree = $tache->durees->last();
            if(isset($duree))
            {
                if(!($this->verifieDuree($duree->toArray(), $attributs))) throw new Exception( "Durée antérieur à celle qui était la", 500);
            }*/

            if($tache->durees->count() > 1){
                foreach ($tache->durees as $key => $index) {
                    if ($index->id === $duree->id) {
                        if($key != 0 && $key != ($tache->durees->count()-1)){
                            if($this->verifieDuree(($tache->durees[$key-1])->toArray(), $attributs)){
                                throw new Exception("Durée antérieur à celle qui était la", 500);
                            }
                            if($attributs['fin'] >= $tache->durees[$key+1]['debut']) {
                                throw new Exception("Durée superieur à celle qui suit", 500);
                            }
                        }
                        else if($key === 0){
                            if($attributs['fin'] >= $tache->durees[$key+1]['debut']) {
                                throw new Exception("Durée superieur à celle qui suit", 500);
                            }
                        }
                        else if($key === $tache->durees->count()){
                            if($this->verifieDuree(($tache->durees[$key-2])->toArray(), $attributs)){
                                throw new Exception("Durée antérieur à celle qui était la", 500);
                            }
                        }
                    }
                }
            }

            $duree->debut = $attributs['debut'];
            $duree->fin = $attributs['fin'];
            $duree->save();

            DB::commit();
            GenererPta::dispatch(Auth::user()->programme)->delay(5);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $duree, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deplacer(array $attributs, $id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($tache = $this->repository->findById($id))) throw new Exception( "Cette tache n'existe pas", 500);

            if($attributs['toPermute'])
            {
                $secondetache = Tache::where('id', $attributs['tacheId'])->get();

                if($tache->activite->id != $secondetache->activite->id) throw new Exception( "Les deux tache n'appartiennent pas à la même activite", 500);

                $temp = $tache->position;
                $tache->position = $secondetache->position;
                $secondetache->position = $temp;

                $tache->save();
                $secondetache->save();
            }

            else
            {
                if($tache->position < $attributs['position'])
                {

                    $taches = Tache::where('composanteId', $tache->activite->id)->
                                           where('position', '<=', $attributs['position'])->
                                           where('position', '>', $tache->position)->
                                           get();

                    if(count($taches))
                    {
                        foreach($taches as $t)
                        {
                            $t->position--;
                            $t->save();
                        }
                    }

                }
                else
                {
                    $taches = tache::where('composanteId', $tache->activite->id)->
                                       where('position', '>=', $attributs['position'])->
                                       where('position', '<', $tache->position)->
                                       get();

                    if(count($taches))
                    {
                        foreach($taches as $t)
                        {
                            $t->position++;
                            $t->save();
                        }
                    }

                }

                $tache->position = $attributs['position'];
                $tache->save();

            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => 'Deplacement effectué', 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
