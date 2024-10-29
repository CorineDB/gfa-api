<?php

namespace App\Services;

use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Models\FormulaireDeGouvernance;
use App\Models\QuestionDeGouvernance;
use App\Models\Soumission;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\QuestionDeGouvernanceRepository;
use App\Repositories\SoumissionRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SoumissionServiceInterface;
use App\Traits\Helpers\HelperTrait;
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
    use HelperTrait;

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

            if(isset($attributs['evaluationId'])){
                if(!(($evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId'])) && $evaluationDeGouvernance->programmeId == $programme->id))
                {
                    throw new Exception( "Evaluation de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            }

            if(isset($attributs['formulaireDeGouvernanceId'])){
                if(!(($formulaireDeGouvernance = app(FormulaireDeGouvernanceRepository::class)->findById($attributs['formulaireDeGouvernanceId'])) && $formulaireDeGouvernance->programmeId == $programme->id))
                {
                    throw new Exception( "Formulaire de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            }

            if(isset($attributs['organisationId'])){

                if(!(($organisation = app(OrganisationRepository::class)->findById($attributs['organisationId'])) && $organisation->user->programmeId == $programme->id))
                {
                    throw new Exception( "Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            }
            else if(Auth::user()->hasRole('organisation')){
                $organisation = Auth::user()->profilable;
            }

            /*dd(Soumission::where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->get());

            dd($attributs);*/

            if(($soumission = $this->repository->getInstance()->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->first()) == null)
            {
                $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
                $soumission = $this->repository->create($attributs);
            }

            $soumission->refresh();

            $soumission->type = $soumission->formulaireDeGouvernance->type;

            $soumission->save();

            if($attributs['response_data']['factuel']){
                foreach ($attributs['response_data']['factuel'] as $key => $item) {

                    if(!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id))
                    {
                        throw new Exception( "Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);

                    if(!$option && $option->programmeId == $programme->id) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())){
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                    }
                    else{
                        $reponseDeLaCollecte->fill(array_merge($item, ['type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                        $reponseDeLaCollecte->save();
                    }

                    if(isset($attributs['preuves']))
                    {
                        foreach($attributs['preuves'] as $preuve)
                        {
                            $this->storeFile($preuve, 'soumissions/preuves/', $reponseDeLaCollecte, null, 'preuves');
                        }
                    }
                }
            }
            else if($attributs['response_data']['perception']){
                foreach ($attributs['response_data']['perception'] as $key => $item) {

                    if(!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id))
                    {
                        throw new Exception( "Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);

                    if(!$option && $option->programmeId == $programme->id) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())){
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                    }
                    else{
                        $reponseDeLaCollecte->fill(array_merge($item, ['type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                        $reponseDeLaCollecte->save();
                    }
                }
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