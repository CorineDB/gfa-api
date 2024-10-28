<?php

namespace App\Services;

use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\SoumissionRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SoumissionServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SoumissionServiceInterface
* @package Core\Services\Interfaces
*/
class SoumissionService extends BaseService implements SoumissionServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SoumissionRepository constructor.
     *
     * @param SoumissionRepository $soumissionRepository
     */
    public function __construct(SoumissionRepository $soumissionRepository)
    {
        parent::__construct($soumissionRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $soumissions = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $soumissions = Auth::user()->programme->soumissions;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SoumissionsResource::collection($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($soumissions, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($soumissions) && !($soumissions = $this->repository->findById($soumissions))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SoumissionsResource($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'submitted_at' => now()]);
            
            $soumission = $this->repository->create($attributs);

            $soumission->refresh();

            $soumission->type = $soumission->formulaireDeGouvernance->type;

            $soumission->save();

            if($attributs['response_data']['factuel']){
                foreach ($attributs['response_data']['factuel'] as $key => $item) {

                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();

                    if(!$option ) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    //$options[$option->id] = ['point' => $option_de_reponse['point']];
                    
                    $soumission->reponses_de_la_collecte()->create(array_merge($item, ['questionId' => $item['indicateurDeGouvernanceId'], 'type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                }
            }
            else if($attributs['response_data']['perception']){

            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($soumission));

            LogActivity::addToLog("Enrégistrement", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionsResource($soumission), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($soumissions, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($soumissions) && !($soumissions = $this->repository->findById($soumissions))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $this->repository->update($soumissions->id, $attributs);

            $soumissions->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($soumissions));

            LogActivity::addToLog("Modification", $message, get_class($soumissions), $soumissions->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionsResource($soumissions), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}