<?php

namespace App\Services;

use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Traits\Helpers\LogActivity;
use App\Models\OptionDeReponse;
use App\Repositories\CritereDeGouvernanceRepository;
use App\Repositories\IndicateurDeGouvernanceRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurDeGouvernanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface IndicateurDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class IndicateurDeGouvernanceService extends BaseService implements IndicateurDeGouvernanceServiceInterface
{
    /**
     * @var service
     */
    protected $repository;

    /**
     * IndicateurDeGouvernanceRepository constructor.
     *
     * @param IndicateurDeGouvernanceRepository $indicateurDeGouvernanceRepository
     */
    public function __construct(IndicateurDeGouvernanceRepository $indicateurDeGouvernanceRepository)
    {
        parent::__construct($indicateurDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {

            $indicateurs_de_gouvernance = collect([]);
            
            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $indicateurs_de_gouvernance = Auth::user()->programme->indicateurs_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function allFiltredBy(array $filtres = [], array $columns = ['*'], array $relations = []) : JsonResponse
    {
        try
        {
            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($this->repository->filterBy($filtres, $columns, $relations)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($indicateurId, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($indicateurId) && !($indicateurId = $this->repository->findById($indicateurId))) throw new Exception("Indicateur introuvable", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursDeGouvernanceResource($indicateurId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs, $message = null) : JsonResponse
    {
        DB::beginTransaction();

        try {
            
            /*$principe = null;

            if($attributs["type"]  == "factuel"){
                $principe = app(CritereDeGouvernanceRepository::class)->findById($attributs['principeable_id']);
            }
            else if($attributs["type"] == "perception"){
                $principe = app(PrincipeDeGouvernanceRepository::class)->findById($attributs['principeable_id']);
            }*/

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
            
            $indicateur = $this->repository->create($attributs);

            //$indicateur = $principe->indicateurs_de_gouvernance()->create($attributs);

            /*$options = [];
            
            unset($attributs["can_have_multiple_reponse"]);

            foreach ($attributs["options_de_reponse"] as $key => $option_de_reponse) {
                
                $option = OptionDeReponse::findByKey($option_de_reponse);

                if($attributs["type"] == "perception"){
                    if($principe->type_de_gouvernance->programmeId != $option->programmeId) throw new Exception( "Cette option n'est pas dans le programme", 500);
                }
                else if($attributs["type"] == "factuel"){
                    if($principe->principe_de_gouvernance->type_de_gouvernance->programmeId != $option->programmeId) throw new Exception( "Cette option n'est pas dans le programme", 500);
                }

                array_push($options, $option->id);
            }

            $indicateur->options_de_reponse()->attach($options);

            $indicateur->refresh();*/


            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé l'indicateur de gouvernance {$indicateur->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($indicateur), $indicateur->id);

            return response()->json(['statut' => 'success', 'message' => "Création du mod réussir", 'data' => new IndicateursDeGouvernanceResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($indicateurId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(is_string($indicateurId))
            {
                $indicateur = $this->repository->findById($indicateurId);
            }
            else{
                $indicateur = $indicateurId;
            }
            
            unset($attributs["can_have_multiple_reponse"]);

            $indicateur->fill($attributs)->save();

            $indicateur->refresh();

            /*if($indicateur->type != $attributs["type"]){
                    
                $principe = null;

                if($attributs["type"] == "factuel"){
                    $principe = app(CritereDeGouvernanceRepository::class)->findById($attributs['principeable_id']);
                }
                else if($attributs["type"]  == "perception"){
                    $principe = app(PrincipeDeGouvernanceRepository::class)->findById($attributs['principeable_id']);
                }

                $indicateur->fill(array_merge($attributs, ['principeable_type' => get_class($principe), 'principeable_id' => $principe->id]))->save();

                //$indicateur = $principe->indicateurs_de_gouvernance()->where("id", $indicateur->id)->first()->update($attributs);
                //$indicateur->fill($attributs)->save();
                $indicateur->refresh();
            }
            else{

                $indicateur->fill($attributs)->save();

                $indicateur->refresh();
                //$indicateur = $indicateur->fresh();
            }

            /*$options = [];

            foreach ($attributs["options_de_reponse"] as $key => $option_de_reponse) {
                
                $option = OptionDeReponse::findByKey($option_de_reponse);

                if($attributs["type"] == "perception"){
                    if($indicateur->principeable->type_de_gouvernance->programmeId != $option->programmeId) throw new Exception( "Cette option n'est pas dans le programme", 500);
                }
                else if($attributs["type"] == "factuel"){
                    if($indicateur->principeable->principe_de_gouvernance->type_de_gouvernance->programmeId != $option->programmeId) throw new Exception( "Cette option n'est pas dans le programme", 500);
                }
                array_push($options, $option->id);
            }

            $indicateur->options_de_reponse()->sync($options);*/

            $indicateur->refresh();


            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié l'indicateur de gouvernance {$indicateur->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            return response()->json(['statut' => 'success', 'message' => "Indicateur modifié", 'data' => new IndicateursDeGouvernanceResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Liste des reponses de l'enquete.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function observations($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        
        DB::beginTransaction();


        try {
            if (!($indicateurDeGouvernance = $this->repository->findById($indicateurId)))
                throw new Exception("Cette enquete n'existe pas", 500);

            $responses = $indicateurDeGouvernance->observations;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $responses, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}