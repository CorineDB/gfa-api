<?php

namespace App\Services;

use App\Http\Resources\gouvernance\FormulairesDeGouvernanceResource;
use App\Repositories\CritereDeGouvernanceRepository;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\IndicateurDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
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
            if((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == 'App\\Models\\Administrateur')){
                $formulaires_de_gouvernance = $this->repository->all();
            }
            else{
                $formulaires_de_gouvernance = Auth::user()->programme->formulaires_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulairesDeGouvernanceResource::collection($formulaires_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            $filtres = array_merge($filtres, ['programmeId__eq' => auth()->user()->programmeId]);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulairesDeGouvernanceResource::collection($this->repository->filterBy($filtres, $columns, $relations)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);
            
            $formulaireDeGouvernance = $this->repository->create($attributs);

            if(isset($attributs['factuel']) && $attributs['factuel'] !== null){

                $options = [];

                foreach ($attributs['factuel']["options_de_reponse"] as $key => $option_de_reponse) {

                    $option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']);

                    if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(isset($option_de_reponse['preuveIsRequired'])){
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => $option_de_reponse['preuveIsRequired']];
                    }else{
                        $options[$option->id] = ['point' => $option_de_reponse['point']];
                    }

                }

                $formulaireDeGouvernance->options_de_reponse()->attach($options);

                foreach ($attributs['factuel']["types_de_gouvernance"] as $key => $type_de_gouvernance) {
                    
                    if(!(($typeDeGouvernance = app(TypeDeGouvernanceRepository::class)->findById($type_de_gouvernance['id'])) && $typeDeGouvernance->programmeId == $programmeId))
                    {
                        throw new Exception( "Ce type de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    }

                    /*$typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDeGouvernanceId")->where("position", $type_de_gouvernance['position'])->whereHas("formulaire_de_gouvernance", function($query) use ($programmeId){
                        $query->where('programmeId', $programmeId);
                    })->first();

                    dd($typeDeGouvernanceCategorie);

                    if(!$typeDeGouvernanceCategorie){*/
                        $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $type_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => null, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                    //}

                    foreach ($type_de_gouvernance["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {
                        
                        //if(!($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id']))->where("programmeId", $programmeId)->first()) throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    
                        if(!(($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                        {
                            throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        /*$principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->where("categorieDeGouvernanceId", $typeDeGouvernanceCategorie->id)->where("position", $principe_de_gouvernance['position'])->whereHas("formulaire_de_gouvernance", function($query) use ($programmeId){
                            $query->where('programmeId', $programmeId);
                        })->first();
    
                        if(!$principeDeGouvernanceCategorie){*/
                            $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $principe_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => $typeDeGouvernanceCategorie->id, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                        //}

                        foreach ($principe_de_gouvernance["criteres_de_gouvernance"] as $key => $critere_de_gouvernance) {
                        
                            //if(!($critereDeGouvernance = app(CritereDeGouvernanceRepository::class)->findById($critere_de_gouvernance['id']))->where("programmeId", $programmeId)->first()) throw new Exception( "Ce critere de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
        
                            if(!(($critereDeGouvernance = app(CritereDeGouvernanceRepository::class)->findById($critere_de_gouvernance['id'])) && $critereDeGouvernance->programmeId == $programmeId))
                            {
                                throw new Exception( "Ce critere de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }

                            /*$critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->where("categorieDeGouvernanceId", $principeDeGouvernanceCategorie->id)->where("position", $critere_de_gouvernance['position'])->whereHas("formulaire_de_gouvernance", function($query) use ($programmeId){
                                $query->where('programmeId', $programmeId);
                            })->first();
        
                            if(!$critereDeGouvernanceCategorie){*/
                                $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $critere_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => $principeDeGouvernanceCategorie->id, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                            //}

                            foreach ($critere_de_gouvernance["indicateurs_de_gouvernance"] as $key => $indicateur_de_gouvernance) {
                        
                                //if(!($indicateurDeGouvernance = app(IndicateurDeGouvernanceRepository::class)->findById($indicateur_de_gouvernance))) throw new Exception( "Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                                if(!(($indicateurDeGouvernance = app(IndicateurDeGouvernanceRepository::class)->findById($indicateur_de_gouvernance))))
                                {
                                    throw new Exception( "Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                }

                                $critereDeGouvernanceCategorie->questions_de_gouvernance()->create(['type' => 'indicateur', /* "position" => $indicateur_de_gouvernance['position'], */ 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurDeGouvernanceId' => $indicateurDeGouvernance->id]);

                            }
                        }

                    }
                }
            }

            if(isset($attributs['perception']) && $attributs['perception'] !== null){

                $options = [];

                foreach ($attributs['perception']["options_de_reponse"] as $key => $option_de_reponse) {
                    
                    $option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']);

                    if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);


                    if(isset($option_de_reponse['preuveIsRequired'])){
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => $option_de_reponse['preuveIsRequired']];
                    }else{
                        $options[$option->id] = ['point' => $option_de_reponse['point']];
                    }

                }

                $formulaireDeGouvernance->options_de_reponse()->attach($options);

                foreach ($attributs['perception']["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {
                        
                    //if(!($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id']))->where("programmeId", $programmeId)->first()) throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    if(!(($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                    {
                        throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    }

                    /*$principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDeGouvernanceId")->where("position", $principe_de_gouvernance['position'])->whereHas("formulaire_de_gouvernance", function($query) use ($programmeId){
                        $query->where('programmeId', $programmeId);
                    })->first();

                    if(!$principeDeGouvernanceCategorie){*/
                        $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $principe_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => null, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                    //}

                    foreach ($principe_de_gouvernance["questions_operationnelle"] as $key => $question_operationnelle) {
                    
                        //if(!($indicateurDeGouvernance = app(IndicateurDeGouvernanceRepository::class)->findById($question_operationnelle))) throw new Exception( "Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                        if(!(($questionOperationnelle = app(IndicateurDeGouvernanceRepository::class)->findById($question_operationnelle))))
                        {
                            throw new Exception( "Cette question operationnelle n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $question_operationnelle = $principeDeGouvernanceCategorie->questions_de_gouvernance()->create(['type' => 'question_operationnelle', /*"position" => $question_operationnelle['position'],*/ 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurDeGouvernanceId' => $questionOperationnelle->id]);
                    }

                }
            }
            
            $formulaireDeGouvernance->save();
            $formulaireDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FormulairesDeGouvernanceResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        }
        catch (\Throwable $th) {

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

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);
            
            $this->repository->update($formulaireDeGouvernance->id, $attributs);

            $formulaireDeGouvernance->refresh();

            if(isset($attributs['factuel']) && $attributs['factuel'] !== null && $formulaireDeGouvernance->type == 'factuel'){

                $options = [];

                foreach ($attributs['factuel']["options_de_reponse"] as $key => $option_de_reponse) {

                    $option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']);

                    if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(isset($option_de_reponse['preuveIsRequired'])){
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => $option_de_reponse['preuveIsRequired']];
                    }else{
                        $options[$option->id] = ['point' => $option_de_reponse['point']];
                    }

                }
		
                if($formulaireDeGouvernance->type == 'factuel'){

                    $formulaireDeGouvernance->options_de_reponse()->sync($options);
                                
                    $categories_de_gouvernance = [];

                    foreach ($attributs['factuel']["types_de_gouvernance"] as $key => $type_de_gouvernance) {
                        
                        if(!(($typeDeGouvernance = app(TypeDeGouvernanceRepository::class)->findById($type_de_gouvernance['id'])) && $typeDeGouvernance->programmeId == $programmeId))
                        {
                            throw new Exception( "Ce type de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDeGouvernanceId")->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                            $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                        })->first();

                        if(!$typeDeGouvernanceCategorie){
                            $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $type_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => null, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                        }

                        $categories_de_gouvernance[] = $typeDeGouvernanceCategorie->id;
                        
                        foreach ($type_de_gouvernance["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {
                            
                            if(!(($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                            {
                                throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }

                            $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->where('programmeId', $programmeId)->where('categorieDeGouvernanceId', $typeDeGouvernanceCategorie->id,)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                                $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                            })->first();

                            if(!$principeDeGouvernanceCategorie){
                                $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $principe_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => $typeDeGouvernanceCategorie->id, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                            }

                            $categories_de_gouvernance[] = $principeDeGouvernanceCategorie->id;

                            foreach ($principe_de_gouvernance["criteres_de_gouvernance"] as $key => $critere_de_gouvernance) {
                            
                                if(!(($critereDeGouvernance = app(CritereDeGouvernanceRepository::class)->findById($critere_de_gouvernance['id'])) && $critereDeGouvernance->programmeId == $programmeId))
                                {
                                    throw new Exception( "Ce critere de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                }
    
                                $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->where('programmeId', $programmeId)->where('categorieDeGouvernanceId', $principeDeGouvernanceCategorie->id)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                                    $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                                })->first();
    
                                if(!$critereDeGouvernanceCategorie){
                                    $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $principe_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => $principeDeGouvernanceCategorie->id, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                                }
                                
                                $categories_de_gouvernance[] = $critereDeGouvernanceCategorie->id;
                                
                                $questions = [];

                                $questions_de_gouvernance = [];
                                                    
                                foreach ($critere_de_gouvernance["indicateurs_de_gouvernance"] as $key => $indicateur_de_gouvernance) {

                                    if(is_array($indicateur_de_gouvernance)){
                                        $id = $indicateur_de_gouvernance['id'];
                                    }
                                    else{
                                        $id = $indicateur_de_gouvernance;
                                    }

                                    if(!(($indicateurDeGouvernance = app(IndicateurDeGouvernanceRepository::class)->findById($id))))
                                    {
                                        throw new Exception( "Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                    }

                                    $questionDeGouvernance = $critereDeGouvernanceCategorie->questions_de_gouvernance()->where('type', 'indicateur')->where('programmeId', $programmeId)->where('formulaireDeGouvernanceId', $formulaireDeGouvernance->id)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                                        $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                                    })->first();
                
                                    if(!$questionDeGouvernance){
                                        $questionDeGouvernance = $critereDeGouvernanceCategorie->questions_de_gouvernance()->create(['type' => 'indicateur', /*"position" => $question_operationnelle['position'],*/ 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurDeGouvernanceId' => $indicateurDeGouvernance->id]);
                                    }
        
                                    $questions[] = $questionDeGouvernance->id;
                                }

                                $formulaireDeGouvernance->questions_de_gouvernance()->where('categorieDeGouvernanceId', $critereDeGouvernanceCategorie->id)->whereNotIn('id', $questions)->delete();

                                //$formulaireDeGouvernance->categorie_de_gouvernance()->sync($questions_de_gouvernance);
                            }
                        }
                    }

                    $categories_de_gouvernance = $formulaireDeGouvernance->all_categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance);

                    $categories_de_gouvernance->delete();

                    //$formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();
                    //$formulaireDeGouvernance->categorie_de_gouvernance()->sync($categories_de_gouvernance);
                }
            }

            if(isset($attributs['perception']) && $attributs['perception'] !== null){

                $options = [];

                foreach ($attributs['perception']["options_de_reponse"] as $key => $option_de_reponse) {
                    
                    $option = app(OptionDeReponseRepository::class)->findById($option_de_reponse['id']);

                    if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(isset($option_de_reponse['preuveIsRequired'])){
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => $option_de_reponse['preuveIsRequired']];
                    }else{
                        $options[$option->id] = ['point' => $option_de_reponse['point']];
                    }

                }

                $formulaireDeGouvernance->options_de_reponse()->sync($options);

                if($formulaireDeGouvernance->type == 'perception'){
                                
                    $categories_de_gouvernance = [];

                    foreach ($attributs['perception']["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {
                            
                        if(!(($principeDeGouvernance = app(PrincipeDeGouvernanceRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                        {
                            throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDeGouvernanceId")->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                            $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                        })->first();

                        if(!$principeDeGouvernanceCategorie){
                            $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, /* "position" => $principe_de_gouvernance['position'], */ 'categorieDeGouvernanceId' => null, 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
                        }
                        
                        $categories_de_gouvernance[] = $principeDeGouvernanceCategorie->id;
                                
                        $questions = [];
                        
                        foreach ($principe_de_gouvernance["questions_operationnelle"] as $key => $question_operationnelle) {
                        
                            if(!(($questionOperationnelle = app(IndicateurDeGouvernanceRepository::class)->findById($question_operationnelle))))
                            {
                                throw new Exception( "Cette question operationnelle n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }

                            $questionDeGouvernance = $principeDeGouvernanceCategorie->questions_de_gouvernance()->where('type', 'question_operationnelle')->where('indicateurDeGouvernanceId', $questionOperationnelle->id)->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                                $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                            })->first();
        
                            if(!$questionDeGouvernance){
                                $questionDeGouvernance = $principeDeGouvernanceCategorie->questions_de_gouvernance()->create(['type' => 'question_operationnelle', /*"position" => $question_operationnelle['position'],*/ 'formulaireDeGouvernanceId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurDeGouvernanceId' => $questionOperationnelle->id]);
                            }

                            $questions[] = $questionDeGouvernance->id;

                            /* 
                            // Fix: Make sure the ID is used as the key
                            $questions_de_gouvernance[$questionOperationnelle->id] = [
                                'categorieDeGouvernanceId' => $principeDeGouvernanceCategorie->id,
                                'type' => 'question_operationnelle',
                                'programmeId' => $programmeId,
                                //'indicateurDeGouvernanceId' => $questionOperationnelle->id
                            ]; */

                        }

                        $formulaireDeGouvernance->questions_de_gouvernance()->where('categorieDeGouvernanceId', $principeDeGouvernanceCategorie->id)->whereNotIn('id', $questions)->delete();
                    }

                    $formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();

                    //$formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();

                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FormulairesDeGouvernanceResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
