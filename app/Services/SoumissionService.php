<?php

namespace App\Services;

use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Models\FormulaireDeGouvernance;
use App\Models\Programme;
use App\Models\QuestionDeGouvernance;
use App\Models\Soumission;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\ProgrammeRepository;
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

            if(isset($attributs['programmeId']) && empty($attributs['programmeId'])){
                $programme = $evaluationDeGouvernance = app(ProgrammeRepository::class)->findById($attributs['programmeId']);
            }
            else{
                $programme = Auth::user()->programme;
            }

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
            else{
                $soumission->fill($attributs);
                $soumission->save();
                if($soumission->statut){
                    return response()->json(['statut' => 'success', 'message' => "La soumission a déjà été validée.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            }

            $soumission->refresh();

            $soumission->type = $soumission->formulaireDeGouvernance->type;

            $soumission->save();

            if($attributs['response_data']['factuel']){
                $soumission->fill($attributs['response_data']['factuel']);
                $soumission->save();
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
                $soumission->fill($attributs['response_data']['perception']);
                $soumission->save();
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

            if(($soumission->formulaireDeGouvernance->type == 'factuel' && $soumission->comite_members !== null) || ($soumission->formulaireDeGouvernance->type == 'perception' && $soumission->commentaire !== null && $soumission->sexe !== null && $soumission->age !== null && $soumission->categorieDeParticipant !== null)){

                $responseCount = $soumission->formulaireDeGouvernance->questions_de_gouvernance()->whereHas('reponses', function($query) use ($soumission) {
                    $query->where(function($query){
                        $query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');
                    });

                    //$query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');

                    // Conditionally apply whereHas('preuves_de_verification') if formulaireDeGouvernance type is 'factuel'
                    if ($soumission->formulaireDeGouvernance->type == 'factuel') {
                        $query->whereHas('preuves_de_verification');
                    }

                })->count();

                if($responseCount === $soumission->formulaireDeGouvernance->questions_de_gouvernance->count()){
                    $soumission->submitted_at = now();
                    $soumission->submittedBy  = auth()->id();
                    $soumission->statut       = true;
    
                    $soumission->save();
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

    private function depouillement_interpretation($soumissionId)
    {
        $programme = auth()->user()->programme;

        if(is_string($soumissionId)){

            if(!(($soumission = app(EvaluationDeGouvernanceRepository::class)->findById($soumissionId)) && $soumission->programmeId == $programme->id))
            {
                throw new Exception( "Soumission introuvable dans le programme.", Response::HTTP_NOT_FOUND);
            }
        }
        else $soumission = $soumissionId;

        $reponsesDeLaCollecte = $soumission->reponses_de_la_collecte;

        $soumission->formulaireDeGouvernance->categories_de_gouvernance
        ->each(function($type) use (&$totalIndiceFactuel, &$nbreDeTypes)  { // Iterate over each governance type
            $nbrePrincipe = 0;
            $totalScoreFactuel = 0;
            $type->principes_de_gouvernance->each(function($principle) use(&$nbrePrincipe, &$totalScoreFactuel){ // Iterate over each principle
                // Calculate score_factuel for each principle
                $nbreIndicateurs = 0;//$principle->indicateurs_criteres_de_gouvernance->count(); // Count the indicators
                $totalNote = 0;//$principle->indicateurs_criteres_de_gouvernance->sum('note'); // Sum the notes
    

                $principle->criteres_de_gouvernance->each(function($critere) use(&$nbreIndicateurs, &$totalNote){ // Iterate over each principle
                    // Calculate score_factuel for each principle
                    $nbreIndicateurs+= $critere->indicateurs_de_gouvernance->count(); // Count the indicators
                    $totalNote+= $critere->indicateurs_de_gouvernance->sum('note'); // Sum the notes
                });

                // Calculate score_factuel
                if ($nbreIndicateurs > 0 && $totalNote > 0) {

                    $principle->score_factuel = $totalNote / $nbreIndicateurs;
                } else {
                    $principle->score_factuel = 0; // Handle case with no indicators
                }

                $totalScoreFactuel+=$principle->score_factuel;

                $nbrePrincipe++;
            });

            // Calculate indice_factuel
            if ($nbrePrincipe > 0 && $totalScoreFactuel > 0) {

                $type->indice_factuel = $totalScoreFactuel / $nbrePrincipe;
            } else {
                $type->indice_factuel = 0; // Handle case with no indicators
            }

            // Add the calculated factuel index to the total sum
            $totalIndiceFactuel += $type->indice_factuel;
            $nbreDeTypes++; // Count the number of governance principles
        });

        $types = app(TypeDeGouvernanceRepository::class)->getInstance()->where("programmeId", $programme->id)
            ->get()
            ->load([
                'principes_de_gouvernance.criteres_de_gouvernance.indicateurs_de_gouvernance' => function ($query) use ($enqueteId, $organisationId) {
                    $query->selectRaw('
                        indicateurs_de_gouvernance.*, 
                        SUM(options_de_reponse.note) as note
                    ')/*
                    $query->selectRaw('
                        indicateurs_de_gouvernance.*, 
                        SUM(CASE 
                            WHEN options_de_reponse.slug = "oui" THEN 1 
                            ELSE 0 
                        END) as note
                    ')*/
                    ->leftJoin('reponses_collecter', 'indicateurs_de_gouvernance.id', '=', 'reponses_collecter.indicateurDeGouvernanceId')
                    ->leftJoin('options_de_reponse', 'reponses_collecter.optionDeReponseId', '=', 'options_de_reponse.id')
                    ->where('reponses_collecter.enqueteDeCollecteId', $enqueteId)
                    ->where('reponses_collecter.organisationId', $organisationId) 
                    ->groupBy('indicateurs_de_gouvernance.id'); // Group by the principle (or category) of governance
                }
            ])/*
            ->each(function($type) use (&$totalIndiceFactuel, &$nbreDeTypes)  { // Iterate over each governance type
                $nbrePrincipe = 0;
                $totalScoreFactuel = 0;
                $type->principes_de_gouvernance->each(function($principle) use(&$nbrePrincipe, &$totalScoreFactuel){ // Iterate over each principle
                    // Calculate score_factuel for each principle
                    $nbreIndicateurs = $principle->indicateurs_criteres_de_gouvernance->count(); // Count the indicators
                    $totalNote = $principle->indicateurs_criteres_de_gouvernance->sum('note'); // Sum the notes
        
                    // Calculate score_factuel
                    if ($nbreIndicateurs > 0 && $totalNote > 0) {

                        $principle->score_factuel = $totalNote / $nbreIndicateurs;
                    } else {
                        $principle->score_factuel = 0; // Handle case with no indicators
                    }

                    $totalScoreFactuel+=$principle->score_factuel;

                    $nbrePrincipe++;
                });

                // Calculate indice_factuel
                if ($nbrePrincipe > 0 && $totalScoreFactuel > 0) {

                    $type->indice_factuel = $totalScoreFactuel / $nbrePrincipe;
                } else {
                    $type->indice_factuel = 0; // Handle case with no indicators
                }

                // Add the calculated factuel index to the total sum
                $totalIndiceFactuel += $type->indice_factuel;
                $nbreDeTypes++; // Count the number of governance principles
            })*/
            ->each(function($type) use (&$totalIndiceFactuel, &$nbreDeTypes)  { // Iterate over each governance type
                $nbrePrincipe = 0;
                $totalScoreFactuel = 0;
                $type->principes_de_gouvernance->each(function($principle) use(&$nbrePrincipe, &$totalScoreFactuel){ // Iterate over each principle
                    // Calculate score_factuel for each principle
                    $nbreIndicateurs = 0;//$principle->indicateurs_criteres_de_gouvernance->count(); // Count the indicators
                    $totalNote = 0;//$principle->indicateurs_criteres_de_gouvernance->sum('note'); // Sum the notes
        

                    $principle->criteres_de_gouvernance->each(function($critere) use(&$nbreIndicateurs, &$totalNote){ // Iterate over each principle
                        // Calculate score_factuel for each principle
                        $nbreIndicateurs+= $critere->indicateurs_de_gouvernance->count(); // Count the indicators
                        $totalNote+= $critere->indicateurs_de_gouvernance->sum('note'); // Sum the notes
                    });

                    // Calculate score_factuel
                    if ($nbreIndicateurs > 0 && $totalNote > 0) {

                        $principle->score_factuel = $totalNote / $nbreIndicateurs;
                    } else {
                        $principle->score_factuel = 0; // Handle case with no indicators
                    }

                    $totalScoreFactuel+=$principle->score_factuel;

                    $nbrePrincipe++;
                });

                // Calculate indice_factuel
                if ($nbrePrincipe > 0 && $totalScoreFactuel > 0) {

                    $type->indice_factuel = $totalScoreFactuel / $nbrePrincipe;
                } else {
                    $type->indice_factuel = 0; // Handle case with no indicators
                }

                // Add the calculated factuel index to the total sum
                $totalIndiceFactuel += $type->indice_factuel;
                $nbreDeTypes++; // Count the number of governance principles
            });

            return [
                "indice_factuel" => $totalIndiceFactuel ? $totalIndiceFactuel/$nbreDeTypes : 0,
                "fiche_de_synthese_factuel" => FicheSyntheseEvaluationFactuelleResource::collection($types)
            ];
    }
}