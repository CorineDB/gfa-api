<?php

namespace App\Services;

use App\Repositories\ComposanteRepository;
use App\Repositories\ProjetRepository;
use App\Models\Composante;
use App\Http\Resources\ActiviteResource;
use App\Http\Resources\ComposanteResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Jobs\GenererPta;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ComposanteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
* Interface ComposanteServiceInterface
* @package Core\Services\Interfaces
*/
class ComposanteService extends BaseService implements ComposanteServiceInterface
{
    use Pta, DBStatementTrait;

    /**
     * @var service
     */
    protected $repository, $projetRepository;

    /**
     * ProjetService constructor.
     *
     * @param ComposanteRepository $composanteRepository
     */
    public function __construct(ComposanteRepository $composanteRepository, ProjetRepository $projetRepository)
    {
        parent::__construct($composanteRepository);
        $this->repository = $composanteRepository;
        $this->projetRepository = $projetRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $composantes = [];            
            
            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $composantes = Auth::user()->profilable->projet->composantes;
            } 
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $composantes = Auth::user()->programme->composantes;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ComposanteResource::collection($composantes), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($composanteId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
           if( !($composante = $this->repository->findById($composanteId)) )  throw new Exception( "Cette composante n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($composante->suivis->sortByDesc("created_at")), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(array_key_exists('projetId', $attributs))
            {
                if(!($projet = $this->projetRepository->findById($attributs['projetId']))) throw new Exception( "Ce projet n'existe pas", 500);

                $attributs = array_merge($attributs, [
                    'composanteId' => 0,
                    'position' => $this->position($projet, 'composantes')
                ]);
            }

            else if(array_key_exists('composanteId', $attributs))
            {
                $composanteParent = $this->repository->findById($attributs['composanteId']);

                if(!$composanteParent) throw new Exception( "La composante n'existe pas", 500);

                $attributs = array_merge($attributs, [
                    'projetId' => $composanteParent->projetId,
                    'position' => $this->position($composanteParent, 'sousComposantes')
                ]);
            }

            else throw new Exception( "Pas de projet ni de composante", 500);

            $this->changeState(0);

            $attributs = array_merge($attributs, ['statut' => -1, 'programmeId' => auth()->user()->programmeId]);

            $composante = $this->repository->create($attributs);

            $this->changeState(1);

            $composante = $composante->fresh();

            /*$statut = ['etat' => -2];

            $statuts = $composante->statuts()->create($statut);**/

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($composante));

            //LogActivity::addToLog("Enregistrement", $message, get_class($composante), $composante->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ComposanteResource($composante), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($composanteId, array $attribut = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            $composante = $this->repository->findById($composanteId);

            if(isset($composante))
            {
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new ComposanteResource($composante), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else throw new Exception("Cette composante n'existe pas", 400);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update($composanteId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            // return  $attributs ;
            if(array_key_exists('projetId', $attributs))
            {
                if(!($projet = $this->projetRepository->findById($attributs['projetId']))) throw new Exception( "Ce projet n'existe pas", 500);
                $type = 1;

                $attributs = array_merge($attributs, [ 'composanteId' => 0 ]);

                unset($attributs['projetId']);

            }

            else if(array_key_exists('composanteId', $attributs))
            {
                $type = 0;

                $composanteParent = $this->repository->findById($attributs['composanteId']);

                if(!$composanteParent) throw new Exception( "La composante n'existe pas", 500);

                $attributs = array_merge($attributs, [ 'projetId' => $composanteParent->projetId ]);

                unset($attributs['composanteId']);
            }

            else throw new Exception( "Pas de projet ni de composante", 500);

            if(array_key_exists('position', $attributs)) unset($attributs['position']);

            if((!is_object($composanteId )))
                $composante = $this->repository->findById($composanteId);
            else {
                $composante = $composanteId;
            }

            //$composante->fill($attributs)->save();

            if($type)
            {
                $composante->composanteId = 0;

                $composante->save();
            }

            if(array_key_exists('statut', $attributs) && $attributs['statut'] === -1 ){

                if(!Auth::user()->hasPermissionTo('validation')) throw new Exception( "Vous n'avez pas la permission de faire la validation", 500);

                if($composante->composanteId)
                {
                    $parentStatut = $composante->composante->statut;

                    if($parentStatut < -1)
                    {
                        throw new Exception( "La composante de cette sous composante n'est pas encore validé", 500);
                    }
                }

                else
                {
                    $parentStatut = $composante->projet->statut;

                    if($parentStatut < -1)
                    {
                        throw new Exception( "Le projet de cette composante n'est pas encore validé", 500);
                    }
                }
                $last = $composante->statut;

                $this->verifieStatut($last, $attributs['statut']);

                if($type)
                {
                    if( $last == -2 && $attributs['statut'] != -2 )
                    {
                        $attributs = array_merge($attributs, ['position' => $this->position($composante->projet, 'composantes')]);

                    }
                }
                else
                {
                    if( $last === -2 && $attributs['statut'] !== -2 )
                    {
                        $attributs = array_merge($attributs, ['position' => $this->position($composante->composante, 'sousComposantes')]);

                    }
                }

            }

            $composante->fill($attributs)->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($composante));

            //LogActivity::addToLog("Modification", $message, get_class($composante), $composante->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ComposanteResource($composante), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function sousComposantes($id = null) : JsonResponse
    {

        try
        {
            $sousComposantes = [];

            if( $id !== null ) $composante = $this->repository->findById($id); //Retourner les données du premier composante
            else $composante = $this->repository->firstItem(); //Retourner les données du premier composante

            if($composante) $sousComposantes = $this->triPta($composante->sousComposantes);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ComposanteResource::collection($sousComposantes), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function activites($id) : JsonResponse
    {

        try
        {
            $activites = [];

            if( $id !== null && $id !== 'undefined' ) $composante = $this->repository->findById($id); //Retourner les données du premier composante
            else $composante = $this->repository->firstItem(); //Retourner les données du premier composante

            if($composante) $activites = $this->triPta($composante->activites);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActiviteResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            if(!($composante = $this->repository->findById($id))) throw new Exception( "Cette composante n'existe pas", 500);

            if($attributs['toPermute'])
            {
                $secondeComposante = Composante::where('id', $attributs['composanteId'])->get();

                if($composante->composanteId)
                {
                    if($composante->composanteId != $secondeComposante->composanteId) throw new Exception( "Les deux sous composante n'appartiennent pas à la même composante", 500);

                    $temp = $composante->position;
                    $composante->position = $secondeComposante->position;
                    $secondeComposante->position = $temp;

                    $composante->save();
                    $secondeComposante->save();
                }

                else
                {
                    if($secondeComposante->composanteId) throw new Exception( "La composante {$secondeComposante} n'est pas une composante mais une sous composante", 500);

                    if($composante->projetId != $secondeComposante->projetId) throw new Exception( "Les deux composante n'appartiennent pas au meme projet", 500);

                    $temp = $composante->position;
                    $composante->position = $secondeComposante->position;
                    $secondeComposante->position = $temp;

                    $composante->save();
                    $secondeComposante->save();
                }

                if($composante->composante->id != $secondeComposante->composante->id) throw new Exception( "Les deux activité n'appartiennent pas à la même composante", 500);

                $temp = $composante->position;
                $composante->position = $secondeComposante->position;
                $secondeComposante->position = $temp;

                $composante->save();
                $secondeComposante->save();
            }

            else
            {
                if($composante->composanteId)
                {
                    if($composante->position < $attributs['position'])
                    {
                        $composantes = Composante::where('composanteId', $composante->composanteId)->
                                       where('position', '<=', $attributs['position'])->
                                       where('position', '>', $composante->position)->
                                       get();

                        if(count($composantes))
                        {
                            foreach($composantes as $c)
                            {
                                $c->position--;
                                $c->save();
                            }
                        }
                    }

                    else
                    {
                        $composantes = Composante::where('composanteId', $composante->composanteId)->
                                       where('position', '>=', $attributs['position'])->
                                       where('position', '<', $composante->position)->
                                       get();

                        if(count($composantes))
                        {
                            foreach($composantes as $c)
                            {
                                $c->position++;
                                $c->save();
                            }
                        }
                    }


                    $composante->position = $attributs['position'];
                    $composante->save();
                }

                else
                {
                    if($composante->position < $attributs['position'])
                    {
                        $composantes = Composante::where('projetId', $composante->projetId)->
                                       where('position', '<=', $attributs['position'])->
                                       where('position', '>', $composante->position)->
                                       get();

                        if(count($composantes))
                        {
                            foreach($composantes as $c)
                            {
                                $c->position--;
                                $c->save();
                            }
                        }
                    }

                    else
                    {
                        $composantes = Composante::where('projetId', $composante->projetId)->
                                       where('position', '>=', $attributs['position'])->
                                       where('position', '<', $composante->position)->
                                       get();

                        if(count($composantes))
                        {
                            foreach($composantes as $c)
                            {
                                $c->position++;
                                $c->save();
                            }
                        }
                    }

                    $composante->position = $attributs['position'];
                    $composante->save();
                }

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
