<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel\ListFormulaireDeGouvernanceFactuelResource;
use App\Repositories\enquetes_de_gouvernance\CritereDeGouvernanceFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\FormulaireFactuelDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\OptionDeReponseGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\TypeDeGouvernanceFactuelRepository;
use Core\Services\Contracts\BaseService;
use Exception;
use App\Traits\Helpers\LogActivity;
use Core\Services\Interfaces\enquetes_de_gouvernance\FormulaireFactuelDeGouvernanceServiceInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Interface FormulaireFactuelDeGouvernanceServiceInterface
 * @package Core\Services\Interfaces
 */
class FormulaireFactuelDeGouvernanceService extends BaseService implements FormulaireFactuelDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FormulaireFactuelDeGouvernanceRepository constructor.
     *
     * @param FormulaireFactuelDeGouvernanceRepository $formulaireDeGouvernanceRepository
     */
    public function __construct(FormulaireFactuelDeGouvernanceRepository $formulaireDeGouvernanceRepository)
    {
        parent::__construct($formulaireDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try {
            if ((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == 'App\\Models\\Administrateur')) {
                $formulaires_factuel_de_gouvernance =  $this->repository->all();
            } else {
                $formulaires_factuel_de_gouvernance = Auth::user()->programme->formulaires_factuel_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ListFormulaireDeGouvernanceFactuelResource::collection($formulaires_factuel_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function allFiltredBy(array $filtres = [], array $columns = ['*'], array $relations = []): JsonResponse
    {
        try {
            $filtres = array_merge($filtres, ['programmeId__eq' => auth()->user()->programmeId]);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => ListFormulaireDeGouvernanceFactuelResource::collection($this->repository->filterBy($filtres, $columns, $relations)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($formulaireDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Formulaire de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ListFormulaireDeGouvernanceFactuelResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);

            $formulaireDeGouvernance = $this->repository->create($attributs);

            if (isset($attributs['factuel']) && $attributs['factuel'] !== null) {

                $options = [];

                foreach ($attributs['factuel']["options_de_reponse"] as $key => $option_de_reponse) {

                    $option = app(OptionDeReponseGouvernanceRepository::class)->findById($option_de_reponse['id']);

                    if (!$option && $option->programmeId == $programmeId) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if (isset($option_de_reponse['preuveIsRequired'])) {
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => $option_de_reponse['preuveIsRequired'], 'sourceIsRequired' => $option_de_reponse['preuveIsRequired'], 'descriptionIsRequired' => !$option_de_reponse['preuveIsRequired'], 'programmeId' => $programmeId];
                    } else {
                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'preuveIsRequired' => false, 'sourceIsRequired' => false, 'descriptionIsRequired' => false, 'programmeId' => $programmeId];
                    }
                }

                $formulaireDeGouvernance->options_de_reponse()->attach($options);

                foreach ($attributs['factuel']["types_de_gouvernance"] as $key => $type_de_gouvernance) {

                    if (!(($typeDeGouvernance = app(TypeDeGouvernanceFactuelRepository::class)->findById($type_de_gouvernance['id'])) && $typeDeGouvernance->programmeId == $programmeId)) {
                        throw new Exception("Ce type de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    }

                    $position = isset($type_de_gouvernance['position']) ? $type_de_gouvernance['position'] : $formulaireDeGouvernance->categories_de_gouvernance->count() + 1;

                    $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => null, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);

                    foreach ($type_de_gouvernance["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {

                        if (!(($principeDeGouvernance = app(PrincipeDeGouvernanceFactuelRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId)) {
                            throw new Exception("Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : $typeDeGouvernanceCategorie->categories_de_gouvernance->count() + 1;
                        $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => $typeDeGouvernanceCategorie->id, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);

                        foreach ($principe_de_gouvernance["criteres_de_gouvernance"] as $key => $critere_de_gouvernance) {

                            if (!(($critereDeGouvernance = app(CritereDeGouvernanceFactuelRepository::class)->findById($critere_de_gouvernance['id'])) && $critereDeGouvernance->programmeId == $programmeId)) {
                                throw new Exception("Ce critere de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }
                            $position = isset($critere_de_gouvernance['position']) ? $critere_de_gouvernance['position'] : $principeDeGouvernanceCategorie->categories_de_gouvernance->count() + 1;
                            $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => $principeDeGouvernanceCategorie->id, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);

                            foreach ($critere_de_gouvernance["indicateurs_de_gouvernance"] as $key => $indicateur_de_gouvernance) {

                                $indicateurDeGouvernanceId = $indicateur_de_gouvernance;
                                if(isset($indicateur_de_gouvernance['id']) ){

                                    $indicateurDeGouvernanceId = $indicateur_de_gouvernance['id'];
                                }

                                if (!(($indicateurDeGouvernance = app(IndicateurDeGouvernanceFactuelRepository::class)->findById($indicateurDeGouvernanceId)))) {
                                    throw new Exception("Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                }

                                $position = isset($indicateur_de_gouvernance['position']) ? $indicateur_de_gouvernance['position'] : $critereDeGouvernanceCategorie->questions_de_gouvernance->count() + 1;

                                $critereDeGouvernanceCategorie->questions_de_gouvernance()->create(["position" => $position, 'formulaireFactuelId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurFactuelDeGouvernanceId' => $indicateurDeGouvernance->id]);
                            }
                        }
                    }
                }
            }

            $formulaireDeGouvernance->save();
            $formulaireDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ListFormulaireDeGouvernanceFactuelResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($formulaireDeGouvernance, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Cette option de reponse n'existe pas", 500);

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);

            $this->repository->update($formulaireDeGouvernance->id, $attributs);

            $formulaireDeGouvernance->refresh();

            if (isset($attributs['factuel']) && $attributs['factuel'] !== null) {

                $options = [];
                if (isset($attributs['factuel']["options_de_reponse"]) && $attributs['factuel']["options_de_reponse"] !== null) {

                    foreach ($attributs['factuel']["options_de_reponse"] as $key => $option_de_reponse) {

                        $option = app(OptionDeReponseGouvernanceRepository::class)->findById($option_de_reponse['id']);

                        if (!$option && $option->programmeId == $programmeId) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                        if (isset($option_de_reponse['preuveIsRequired'])) {
                            $options[$option->id] = ['point' => $option_de_reponse['point'], 'programmeId' => $programmeId, 'preuveIsRequired' => $option_de_reponse['preuveIsRequired']];
                        } else {
                            $options[$option->id] = ['point' => $option_de_reponse['point'], 'programmeId' => $programmeId];
                        }
                    }

                    $formulaireDeGouvernance->options_de_reponse()->sync($options);
                }

                if (isset($attributs['factuel']["types_de_gouvernance"]) && $attributs['factuel']["types_de_gouvernance"] !== null) {

                    $categories_de_gouvernance = [];

                    foreach ($attributs['factuel']["types_de_gouvernance"] as $key => $type_de_gouvernance) {

                        if (!(($typeDeGouvernance = app(TypeDeGouvernanceFactuelRepository::class)->findById($type_de_gouvernance['id'])) && $typeDeGouvernance->programmeId == $programmeId)) {
                            throw new Exception("Ce type de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->whereNull("categorieFactuelDeGouvernanceId")->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function ($query) use ($formulaireDeGouvernance, $programmeId) {
                            $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                        })->first();

                        if (!$typeDeGouvernanceCategorie) {
                            $position = isset($type_de_gouvernance['position']) ? $type_de_gouvernance['position'] : $formulaireDeGouvernance->categories_de_gouvernance->count() + 1;
                            $typeDeGouvernanceCategorie = $typeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => null, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);
                        }else{
                            $position = isset($type_de_gouvernance['position']) ? $type_de_gouvernance['position'] : $typeDeGouvernanceCategorie->position;

                            $typeDeGouvernanceCategorie->position = $position;
                            $typeDeGouvernanceCategorie->save();
                        }

                        $categories_de_gouvernance[] = $typeDeGouvernanceCategorie->id;

                        foreach ($type_de_gouvernance["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {

                            if (!(($principeDeGouvernance = app(PrincipeDeGouvernanceFactuelRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId)) {
                                throw new Exception("Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }

                            $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->where('programmeId', $programmeId)->where('categorieFactuelDeGouvernanceId', $typeDeGouvernanceCategorie->id,)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function ($query) use ($formulaireDeGouvernance, $programmeId) {
                                $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                            })->first();

                            if (!$principeDeGouvernanceCategorie) {
                                $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : $typeDeGouvernanceCategorie->categories_de_gouvernance->count() + 1;
                                $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => $typeDeGouvernanceCategorie->id, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);
                            }else{
                                $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : $principeDeGouvernanceCategorie->position;

                                $principeDeGouvernanceCategorie->position = $position;
                                $principeDeGouvernanceCategorie->save();
                            }

                            $categories_de_gouvernance[] = $principeDeGouvernanceCategorie->id;

                            foreach ($principe_de_gouvernance["criteres_de_gouvernance"] as $key => $critere_de_gouvernance) {

                                if (!(($critereDeGouvernance = app(CritereDeGouvernanceFactuelRepository::class)->findById($critere_de_gouvernance['id'])) && $critereDeGouvernance->programmeId == $programmeId)) {
                                    throw new Exception("Ce critere de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                }

                                $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->where('programmeId', $programmeId)->where('categorieFactuelDeGouvernanceId', $principeDeGouvernanceCategorie->id)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function ($query) use ($formulaireDeGouvernance, $programmeId) {
                                    $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                                })->first();

                                if (!$critereDeGouvernanceCategorie) {

                                    $position = isset($critere_de_gouvernance['position']) ? $critere_de_gouvernance['position'] : $principeDeGouvernanceCategorie->categories_de_gouvernance->count() + 1;
                                    $critereDeGouvernanceCategorie = $critereDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieFactuelDeGouvernanceId' => $principeDeGouvernanceCategorie->id, 'formulaireFactuelId' => $formulaireDeGouvernance->id]);
                                }else{
                                    $position = isset($critere_de_gouvernance['position']) ? $critere_de_gouvernance['position'] : $critereDeGouvernanceCategorie->position;

                                    $critereDeGouvernanceCategorie->position = $position;
                                    $critereDeGouvernanceCategorie->save();
                                }

                                $categories_de_gouvernance[] = $critereDeGouvernanceCategorie->id;

                                $questions = [];

                                $questions_de_gouvernance = [];

                                foreach ($critere_de_gouvernance["indicateurs_de_gouvernance"] as $key => $indicateur_de_gouvernance) {
/*
                                    if (is_array($indicateur_de_gouvernance)) {
                                        $id = $indicateur_de_gouvernance['id'];
                                    } else {
                                        $id = $indicateur_de_gouvernance;
                                    } */


                                    $indicateurDeGouvernanceId = $indicateur_de_gouvernance;
                                    if(isset($indicateur_de_gouvernance['id']) ){
                                        $indicateurDeGouvernanceId = $indicateur_de_gouvernance['id'];
                                    }

                                    if (!(($indicateurDeGouvernance = app(IndicateurDeGouvernanceFactuelRepository::class)->findById($indicateurDeGouvernanceId)))) {
                                        throw new Exception("Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                                    }

                                    $questionDeGouvernance = $critereDeGouvernanceCategorie->questions_de_gouvernance()->where('programmeId', $programmeId)->where('formulaireFactuelId', $formulaireDeGouvernance->id)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function ($query) use ($formulaireDeGouvernance, $programmeId) {
                                        $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                                    })->first();

                                    if (!$questionDeGouvernance) {
                                        $position = isset($indicateur_de_gouvernance['position']) ? $indicateur_de_gouvernance['position'] : $critereDeGouvernanceCategorie->questions_de_gouvernance->count() + 1;
                                        $questionDeGouvernance = $critereDeGouvernanceCategorie->questions_de_gouvernance()->create(["position" => $position, 'formulaireFactuelId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'indicateurFactuelDeGouvernanceId' => $indicateurDeGouvernance->id]);
                                    }else{
                                        $position = isset($indicateur_de_gouvernance['position']) ? $indicateur_de_gouvernance['position'] : $questionDeGouvernance->position;

                                        $questionDeGouvernance->position = $position;
                                        $questionDeGouvernance->save();
                                    }

                                    $questions[] = $questionDeGouvernance->id;
                                }

                                $formulaireDeGouvernance->questions_de_gouvernance()->where('categorieFactuelDeGouvernanceId', $critereDeGouvernanceCategorie->id)->whereNotIn('id', $questions)->delete();

                                //$formulaireDeGouvernance->categorie_de_gouvernance()->sync($questions_de_gouvernance);
                            }
                        }
                    }

                    //$categories_de_gouvernance = $formulaireDeGouvernance->all_categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance);


                    $categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();

                    $categories_de_gouvernance->delete();
                }
                //$formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();
                //$formulaireDeGouvernance->categorie_de_gouvernance()->sync($categories_de_gouvernance);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ListFormulaireDeGouvernanceFactuelResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
