<?php

namespace App\Services;

use App\Http\Resources\gouvernance\FormulairesDeGouvernanceResource;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\TypeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\FormulaireDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface FormulaireDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class FormulaireDeGouvernanceService extends BaseService implements FormulaireDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FormulaireDeGouvernanceRepository constructor.
     *
     * @param FormulaireDeGouvernanceRepository $formulaireDeGouvernanceRepository
     */
    public function __construct(FormulaireDeGouvernanceRepository $formulaireDeGouvernanceRepository)
    {
        parent::__construct($formulaireDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $formulairesDeGouvernance = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $formulairesDeGouvernance = Auth::user()->programme->formualaires_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulairesDeGouvernanceResource::collection($formulairesDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($formulaireDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Formulaire de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new FormulairesDeGouvernanceResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'created_by' => Auth::id()]);
            
            $formulaireDeGouvernance = $this->repository->create($attributs);

            if(isset($attributs['factuel']) && $attributs['factuel'] !== null){

                $options = [];

                foreach ($attributs['factuel']["options_de_reponse"] as $key => $option_de_reponse) {
                    
                    $option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']);

                    if(!($option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']))) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    array_push($options, ["{$option->id}" => $option_de_reponse['point']]);
                }

                $formulaireDeGouvernance->options_de_reponse()->attach($options);

                foreach ($attributs['factuel']["types_de_gouvernance"] as $key => $type_de_gouvernance) {
                    

                    if(!($typeDeGouvernance = app(TypeDeGouvernanceRepository::class)->findById($type_de_gouvernance['id']))) throw new Exception( "Ce type de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);


                }
                /**
                 * Check if type
                 */
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($formulaireDeGouvernance));

            LogActivity::addToLog("Enrégistrement", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FormulairesDeGouvernanceResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($formulaireDeGouvernance, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Cette option de reponse n'existe pas", 500);

            $this->repository->update($formulaireDeGouvernance->id, $attributs);

            $formulaireDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($formulaireDeGouvernance));

            LogActivity::addToLog("Modification", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FormulairesDeGouvernanceResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}