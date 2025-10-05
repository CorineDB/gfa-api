<?php

namespace App\Services;

use App\Http\Resources\indicateur\IndicateurResource;
use App\Http\Resources\indicateur\IndicateursResource;
use App\Http\Resources\suivi\SuiviIndicateurResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Models\Indicateur;
use App\Models\IndicateurValueKey;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Unitee;
use App\Models\UniteeDeGestion;
use App\Repositories\BailleurRepository;
use App\Repositories\CategorieRepository;
use App\Repositories\IndicateurRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\SiteRepository;
use App\Repositories\UniteeMesureRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use App\Repositories\ValeurCibleIndicateurRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Interface IndicateurServiceInterface
 * @package Core\Services\Interfaces
 */
class IndicateurService extends BaseService implements IndicateurServiceInterface
{

    use DBStatementTrait;

    /**
     * @var service
     */
    protected $valeurCibleIndicateurRepository;
    protected $categorieRepository;
    protected $uniteeMesureRepository;
    protected $userRepository;
    protected $bailleurRepository;
    protected $repository;

    /**
     * IndicateurRepository constructor.
     *
     * @param IndicateurRepository $indicateurRepository
     */
    public function __construct(IndicateurRepository $indicateurRepository, UniteeMesureRepository $uniteeMesureRepository, CategorieRepository $categorieRepository, UserRepository $userRepository, BailleurRepository $bailleurRepository, ValeurCibleIndicateurRepository $valeurCibleIndicateurRepository)
    {
        parent::__construct($indicateurRepository);
        $this->repository = $indicateurRepository;
        $this->uniteeMesureRepository = $uniteeMesureRepository;
        $this->userRepository = $userRepository;
        $this->bailleurRepository = $bailleurRepository;
        $this->categorieRepository = $categorieRepository;
        $this->valeurCibleIndicateurRepository = $valeurCibleIndicateurRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $indicateurs = [];

            if (Auth::user()->hasRole('bailleur')) {
                $indicateurs = /* Cache::remember('indicateurs-bailleur'.Auth::user()->profilable->id, 60, function(){*/
                    Indicateur::where('programmeId', Auth::user()->programmeId)
                    ->where('bailleurId', Auth::user()->profilable->id)
                    ->get();
                //});
            } elseif (Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)) {
                $indicateurs = Auth::user()->profilable->indicateurs;
            } else if (Auth::user()->hasRole("unitee-de-gestion") || (get_class(auth()->user()->profilable) == UniteeDeGestion::class)) {

                $indicateurs = Auth::user()->programme->indicateurs;
            }

            // Ancien code
            /* else {

                $indicateurs = [];
                $bailleurs = Auth::user()->programme->bailleurs->load('profilable')->pluck("profilable");

                foreach ($bailleurs as $bailleur) {
                    $bindicateurs = Indicateur::where('programmeId', Auth::user()->programmeId)
                        ->where('bailleurId', $bailleur->id)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($bindicateurs as $indicateur) {
                        array_push($indicateurs, $indicateur);
                    }
                }
            }*/


            //$indicateurs = Indicateur::where('programmeId', Auth::user()->programmeId)->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursResource::collection($indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filtre(array $attributs): JsonResponse
    {

        try {

            if (!array_key_exists('nom', $attributs)) {
                $attributs = array_merge($attributs, ['nom' => '']);
            }

            if (!array_key_exists('anneeDeBase', $attributs)) {
                $attributs = array_merge($attributs, ['anneeDeBase' => '']);
            }

            if (!array_key_exists('uniteeDeMesureId', $attributs)) {
                $attributs = array_merge($attributs, ['uniteeDeMesureId' => '']);
            }

            if (!array_key_exists('categorieId', $attributs)) {
                $attributs = array_merge($attributs, ['categorieId' => '']);
            }

            if (!array_key_exists('bailleurId', $attributs)) {
                $attributs = array_merge($attributs, ['bailleurId' => '']);
            }

            $indicateurs = Indicateur::where('nom', "like", "%{$attributs['nom']}%")->where('anneeDeBase', 'like', "%{$attributs['anneeDeBase']}%")->where('uniteeMesureId', 'like', "%{$attributs['uniteeDeMesureId']}%")->where('categorieId', 'like', "%{$attributs['categorieId']}%")->where('bailleurId', 'like', "%{$attributs['bailleurId']}%")->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursResource::collection($indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verife suivi.
     *
     * @param  $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function checkSuivi($idIndicateur, $year): JsonResponse
    {

        try {

            if (!$indicateur = $this->repository->findById($idIndicateur)) throw new Exception("Indicateur inconnu", 500);

            $suivisIndicateur = $indicateur->valeursCible->where("annee", $year)->count();

            //$suivisIndicateur = $this->repository->all()->load("valeurCible")->pluck("valeurCible")->where(["cibleable_type" => "App\Models\Indicateur", "cibleable_id" => $idIndicateur, "annee" => $year])->count();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivisIndicateur > 0 ? true : false, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($indicateur = $this->repository->findById($indicateurId)))  throw new Exception("Cet indicateur n'existe pas", 500);

            $suivis = [];

            if (Auth::user()->hasRole("organisation")) {
                $suivis = $indicateur->suivis->pluck("suivisIndicateur")->collapse()->sortByDesc("created_at");
            } else if (Auth::user()->hasRole("unitee-de-gestion")) {
                $suivis = $indicateur->suivis->pluck("suivisIndicateur")->collapse()->sortByDesc("created_at");
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuiviIndicateurResource::collection($suivis), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rechercher une occurence de donnée d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Illuminate\Http\JsonResponse
     */
    public function findById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): JsonResponse {

        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            $message = $th->getMessage();

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            if (str_contains($message, "No query results for model")) {

                $message = "Aucun résultats";

                $code = Response::HTTP_NOT_FOUND;
            }

            return response()->json(['statut' => 'error', 'message' => $message, 'errors' => [], 'statutCode' => $code], $code);
        }
    }

    /**
     * Création d'un indicateur
     *
     *
     */
    public function create($attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if (array_key_exists('categorieId', $attributs) && !($attributs['categorieId'] = $this->categorieRepository->findById($attributs['categorieId']))) throw new Exception("Catégorie inconnue", 404);

            else if (isset($attributs['categorieId']) && $attributs['categorieId']->id) $attributs['categorieId'] = $attributs['categorieId']->id;

            else  $attributs['categorieId'] = 0;

            $unitee = null;

            if (isset($attributs["uniteeMesureId"])) {

                if (!($unitee = Unitee::find($attributs['uniteeMesureId']))) {
                    throw new Exception("Unitee de mesure inconnue", 404);
                }
            }

            if (isset($attributs["anneeDeBase"]) && !is_null($attributs["anneeDeBase"])) {

                $anneeDeBase = Carbon::createFromFormat('Y', $attributs['anneeDeBase'])->format('Y');

                /* if (Carbon::parse($programme->debut)->year < $anneeDeBase && $anneeDeBase < Carbon::parse($programme->fin)->year) {
                    throw new Exception("L'année de base doit être une date postérieure ou égale à ".$attributs['anneeDeBase'].".", 422);
                } */
                if (Carbon::parse($programme->debut)->year > $anneeDeBase && $anneeDeBase > Carbon::parse($programme->fin)->year) {
                    throw new Exception("L'année de base doit être une date postérieure ou égale à " . $attributs['anneeDeBase'] . ".", 422);
                }
            }

            /*if ($unitee->type && !ctype_digit($attributs['valeurDeBase'])) {
                throw new Exception("La valeur de base ne doit pas contenir de lettre à cause de l'unitée de mesure sélectionnée", 422);
            }*/

            //$indicateur = $this->repository->fill(array_merge($attributs, ["bailleurId" => $attributs['bailleurId'], "uniteeMesureId" => $attributs['uniteeMesureId'], "categorieId" => $attributs['categorieId']]));
            $valeursDeBase = null;

            if (isset($attributs["valeurDeBase"]) && !is_null($attributs["valeurDeBase"])) {
                $valeursDeBase = $attributs["valeurDeBase"];
                unset($attributs["valeurDeBase"]);
            }

            unset($attributs["bailleurId"]);
            $indicateur = $this->repository->create($attributs);

            $this->attachValueKeys($indicateur, $attributs);

            /*
                if (isset($attributs["value_keys"])) {
                    foreach ($attributs["value_keys"] as $key => $value_key) {

                        $indicateurValueKey = IndicateurValueKey::find($value_key['id']);

                        if (!$indicateurValueKey) {
                            throw new Exception("Cle d'indicateur inconnue.", 404);
                        }

                        $uniteeMesure = isset($value_key["uniteeMesureId"]) ? (optional(Unitee::find($value_key['uniteeMesureId'])) ?? $indicateurValueKey->uniteeMesure) : ($unitee ? $unitee : $indicateurValueKey->uniteeMesure);
                        $indicateur->valueKeys()->attach($indicateurValueKey->id, ["uniteeMesureId" => $uniteeMesure->id, "type" => $uniteeMesure->nom]);
                    }
                } else {

                    $indicateurValueKey = IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first();

                    if (!$indicateurValueKey) {
                        throw new Exception("Cle d'indicateur inconnu.", 404);
                    }

                    if (!$unitee) {
                        if (!isset($attributs["uniteeMesureId"])) {
                            throw new Exception("Veuillez preciser l'unite de mesure de l'indicateur inconnue", 404);
                        }

                        if (!($unitee = Unitee::find($attributs['uniteeMesureId']))) {
                            throw new Exception("Unitee de mesure inconnue", 404);
                        }
                    }
                    $indicateur->valueKeys()->attach($indicateurValueKey->id, ["uniteeMesureId" => $unitee->id, "type" => $unitee->nom]);
                }
            */

            //$indicateurKeys = $indicateur->valueKeys()->whereIn("indicateur_value_keys.id", collect($valeursDeBase)->pluck('key')->toArray())->get();

            if (isset($attributs['value_keys'])) {

                // Check if the number of items in 'value_keys' exceeds the number of items in 'valeursDeBase'
                if (count($attributs['value_keys']) > count($valeursDeBase)) {
                    // If the condition is true, throw an exception with an error message
                    // The message indicates that each value key of the indicator should have a corresponding base value
                    throw new Exception("La demande n'a pas pu être traitée : les valeurs de chaque clé de l'indicateur doivent être précisées dans la valeur de base. Veuillez vérifier les données fournies.", 1);
                }

                // Extract the 'id' field from 'value_keys' and 'keyId' field from 'valeursDeBase' into collections
                // Then, compute the difference to identify any 'value_keys' that are missing in 'valeursDeBase'
                $diff = collect($attributs['value_keys'])->pluck('id')->diff(collect($valeursDeBase)->pluck('keyId'));

                // Check if the $diff collection is not empty, which means some 'value_keys' are missing in 'valeursDeBase'
                if ($diff->isNotEmpty()) {
                    // If the condition is true, throw an exception with an error message
                    // The message explains that the base values must correspond to all the value keys for the indicator
                    throw new Exception("La demande n'a pas pu être traitée : les valeurs de chaque clé de l'indicateur doivent être précisées dans la valeur de base. Veuillez vérifier les données fournies.", 1);
                }
            }

            if (!is_null($valeursDeBase)) {
                $valeurDeBase = $this->setIndicateurValue($indicateur, $programme, $valeursDeBase);

                $indicateur->valeurDeBase = $valeurDeBase;
            }

            /*
                if (is_array($valeursDeBase)) {
                    foreach ($valeursDeBase as $key => $item) {
                        if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $item['keyId'])->first())) {
                            $valeur = $indicateur->valeursDeBase()->create(["value" => $item["value"], "indicateurValueKeyMapId" => $key->pivot->id]);

                            $valeurDeBase = array_merge($valeurDeBase, ["{$key->key}" => $valeur->value]);
                        }
                    }
                } else {

                    $mapKey = optional($indicateur->valueKey()->pivot)->id ?? (optional((IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first())->pivot)->id ?? null);

                    if (is_null($mapKey)) throw new Exception("Cle d'indicateur inconnu.", 404);

                    $valeur = $indicateur->valeursDeBase()->create(["value" => $valeursDeBase, "indicateurValueKeyMapId" => $mapKey]);

                    $valeurDeBase = array_merge($valeurDeBase, ["{$indicateur->valueKey()->key}" => $valeur->value]);
                }
            */


            if (isset($attributs["anneesCible"]) && !is_null($attributs["anneesCible"])) {
                $this->setIndicateurValeursCible($indicateur, $programme, $attributs["anneesCible"]);
            }

            $this->changeState(0);

            $indicateur->save();

            $this->changeState(1);

            if (isset($attributs['responsables']['ug']) && !is_null($attributs['responsables']['ug'])) {
                $indicateur->ug_responsable()->attach([$attributs['responsables']['ug'] => ["responsableable_type" => UniteeDeGestion::class, "programmeId" => $programme->id, "created_at" => now(), "updated_at" => now()]]);
            }

            if (isset($attributs['responsables']['organisations']) && !is_null($attributs['responsables']['organisations'])) {
                $responsables = [];

                foreach ($attributs['responsables']['organisations'] as $key => $organisation_responsable) {

                    if (!($organisation = app(OrganisationRepository::class)->findById($organisation_responsable))) throw new Exception("Organisation inconnu", 1);

                    // Add directly to the array with the expected format
                    $responsables[$organisation->id] = [
                        "responsableable_type" => Organisation::class,
                        "programmeId" => $programme->id,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }

                $indicateur->organisations_responsable()->attach($responsables);
            }

            if (isset($attributs['sites'])) {

                $sites = [];
                foreach ($attributs['sites'] as $id) {
                    if (!($site = app(SiteRepository::class)->findById($id))) throw new Exception("Site introuvable", Response::HTTP_NOT_FOUND);

                    array_push($sites, $site->id);
                }

                $indicateur->sites()->attach($sites, ["programmeId" => $attributs['programmeId']]);
            }

            $indicateur->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateur));

            //LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            Cache::forget('indicateurs');


            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($indicateur, array $attributs): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            } else {
                $indicateur = $indicateur;
            }

            $programme = Auth::user()->programme;

            unset($attributs["programmeId"]);

            if (array_key_exists('categorieId', $attributs) && !($attributs['categorieId'] = $this->categorieRepository->findById($attributs['categorieId']))) throw new Exception("Catégorie inconnue", 404);

            else if (isset($attributs['categorieId']) && $attributs['categorieId']->id) $attributs['categorieId'] = $attributs['categorieId']->id;

            else;

            $unitee = null;

            /*

            if (isset($attributs["uniteeMesureId"])) {

                if (!($unitee = Unitee::find($attributs['uniteeMesureId']))) {
                    throw new Exception("Unitee de mesure inconnue", 404);
                }
            }

            if(isset($attributs['anneeDeBase'])){
                $anneeDeBase = Carbon::createFromFormat('Y', $attributs['anneeDeBase'])->format('Y');

                if (Carbon::parse($programme->debut)->year < $anneeDeBase && $anneeDeBase < Carbon::parse($programme->fin)->year) {
                    throw new Exception("L'année de base doit être une date postérieure ou égale à 2022.", 422);
                }
            }

            unset($attributs["bailleurId"]);

            if($indicateur->suivis) unset($attributs['agreger']);

            $oldValeursDeBase = null;

            if (isset($attributs['agreger']) && $indicateur->agreger != $attributs['agreger'] && $indicateur->suivi->count() == 0) {
                $oldValeursDeBase = $indicateur->valeursDeBase;

                $this->attachValueKeys($indicateur, $attributs);

                $oldValeursDeBase->delete();
            }
            else {
                unset($attributs['valeur_keys']);
            }

            if(isset($attributs["valeurDeBase"])){

                $valeursDeBase = $attributs["valeurDeBase"];

                unset($attributs["valeurDeBase"]);

                if(isset($attributs["agreger"]) && $attributs["agreger"]){

                }
                else{

                }

                $result = DB::table('indicateur_valeurs')
                    ->join('indicateur_value_keys_mapping', 'indicateur_valeurs.indicateurValueKeyMapId', '=', 'indicateur_value_keys_mapping.indicateurValueId')
                    ->where('indicateur_value_keys_mapping.indicateurId', $indicateur->id)
                    ->whereIn('indicateur_value_keys_mapping.indicateurValueKeyId', collect($valeursDeBase)->pluck('keyId'))
                    ->update([
                        'indicateur_valeurs.value' => DB::raw('
                            CASE
                                ' . collect($valeursDeBase)->map(function ($valeur) {
                                    return "WHEN indicateur_value_keys_mapping.indicateurValueKeyId = '{$valeur['keyId']}' THEN '{$valeur['value']}'";
                                })->join(' ') . '
                            END
                        '),
                        'indicateur_valeurs.updated_at' => now()
                    ]);

                dd($result);

                $diffValeursDeBase = collect($valeursDeBase)->pluck('value')->diff($indicateur->valeursDeBase->pluck('value'));

                // Check if the $diffValeursDeBase collection is not empty, which means some 'value_keys' are missing in 'valeursDeBase'
                if ($diffValeursDeBase->isNotEmpty()) {

                    // Check if the number of items in 'value_keys' exceeds the number of items in 'valeursDeBase'
                    if ($indicateur->valueKeys->count() > count($valeursDeBase)) {
                        // If the condition is true, throw an exception with an error message
                        // The message indicates that each value key of the indicator should have a corresponding base value
                        throw new Exception("La demande n'a pas pu être traitée : les valeurs de chaque clé de l'indicateur doivent être précisées dans la valeur de base. Veuillez vérifier les données fournies.", 1);
                    }

                    // Extract the 'id' field from 'value_keys' and 'keyId' field from 'valeursDeBase' into collections
                    // Then, compute the difference to identify any 'value_keys' that are missing in 'valeursDeBase'
                    $diffKeys = $indicateur->valueKeys->pluck('id')->diff(collect($valeursDeBase)->pluck('keyId'));

                    // Check if the $diffKeys collection is not empty, which means some 'value_keys' are missing in 'valeursDeBase'
                    if ($diffKeys->isNotEmpty()) {
                        // If the condition is true, throw an exception with an error message
                        // The message explains that the base values must correspond to all the value keys for the indicator
                        throw new Exception("La demande n'a pas pu être traitée : les valeurs de chaque clé de l'indicateur doivent être précisées dans la valeur de base. Veuillez vérifier les données fournies.", 1);
                    }

                    $changeValeurDeBase = $indicateur->valeursDeBase()->whereNotIn("value", collect($valeursDeBase)->pluck('value')->toArray())->get();

                    $valeurDeBase = $indicateur->valeurDeBase;

                    if ($changeValeurDeBase->isNotEmpty()) {

                        $changeValeurDeBase->each(function ($valeur) use ($valeursDeBase, $valeurDeBase, $indicateur) {

                            $valueKey = $indicateur->valueKeys()->withPivot('id')->wherePivot('id', $valeur->indicateurValueKeyMapId)->first();

                            $res = collect($valeursDeBase)->where('keyId', $valueKey->id)->first();

                            $valeur->value = $res['value'];
                            $valeur->save();
                            $valeurDeBase = array_merge($valeurDeBase, ["{$valueKey->key}" => $valeur->value]);
                        });

                        $indicateur->valeurDeBase = $valeurDeBase;
                    }

                    $diffValeursDeBase = collect($valeursDeBase)->pluck('value')->diff($indicateur->valeursDeBase->pluck('value'));

                    $remainValeursDeBase = collect($valeursDeBase)->whereIn("value", $diffValeursDeBase->toArray())->toArray();

                    if (count($remainValeursDeBase)) {

                        if ($indicateur->agreger && is_array($remainValeursDeBase)) {

                            foreach ($remainValeursDeBase as $key => $item) {
                                if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $item['keyId'])->first())) {
                                    $valeur = $indicateur->valeursDeBase()->create(["value" => $item["value"], "indicateurValueKeyMapId" => $key->pivot->id]);

                                    $valeurDeBase = array_merge($valeurDeBase, ["{$key->key}" => $valeur->value]);
                                }
                            }
                        } else if (!$indicateur->agreger && !is_array($remainValeursDeBase)) {

                            $indicateurValueKey = IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first();

                            if (!$indicateurValueKey) {
                                throw new Exception("Cle d'indicateur inconnu.", 404);
                            }

                            $mapKey = optional($indicateur->valueKey()->pivot)->id ?? (optional((IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first())->pivot)->id ?? null);

                            if (is_null($mapKey)) throw new Exception("Cle d'indicateur inconnu.", 404);

                            $valeur = $indicateur->valeursDeBase()->create(["value" => $remainValeursDeBase, "indicateurValueKeyMapId" => $mapKey]);

                            $valeurDeBase = array_merge($valeurDeBase, ["{$indicateurValueKey->key}" => $valeur->value]);
                        } else {
                            throw new Exception("La demande n'a pas pu être traitée : Veuillez préciser la valeur de base dans le format adequat.", 400);
                        }
                    }

                    $indicateur->valeurDeBase = $valeurDeBase;
                }

            }

            $indicateur = $indicateur->fill($attributs);

            if(isset($attributs["anneesCible"])){
                    $oldValeursCible = $indicateur->valeursCible;

                    $this->setIndicateurValeursCible($indicateur, $attributs["anneesCible"]);

                    $oldValeursCible->each->delete();

            }
            */

            $indicateur->nom = $attributs['nom'];
            $indicateur->description = $attributs['description'];

            if (isset($attributs['type_de_variable'])) {
                $indicateur->type_de_variable = $attributs['type_de_variable'];
            }

            if (isset($attributs['hypothese'])) {
                $indicateur->hypothese = $attributs['hypothese'];
            }

            if (isset($attributs['indice'])) {
                $indicateur->indice = $attributs['indice'];
            }

            if (isset($attributs['uniteeMesureId'])) {
                $indicateur->uniteeMesureId = $attributs['uniteeMesureId'];
            }

            if (isset($attributs['categorieId'])) {
                $indicateur->categorieId = $attributs['categorieId'];
            }

            if (isset($attributs['methode_de_la_collecte'])) {
                $indicateur->methode_de_la_collecte = $attributs['methode_de_la_collecte'];
            }

            if (isset($attributs['hypothese'])) {
                $indicateur->hypothese = $attributs['hypothese'];
            }

            if (isset($attributs['frequence_de_la_collecte'])) {
                $indicateur->frequence_de_la_collecte = $attributs['frequence_de_la_collecte'];
            }

            if (isset($attributs['sources_de_donnee'])) {
                $indicateur->sources_de_donnee = $attributs['sources_de_donnee'];
            }

            $this->changeState(0);

            $indicateur->save();

            $this->changeState(1);

            if (isset($attributs['responsables']['ug']) && !is_null($attributs['responsables']['ug'])) {
                $indicateur->ug_responsable()->sync([$attributs['responsables']['ug'] => ["responsableable_type" => UniteeDeGestion::class, "programmeId" => $programme->id, "created_at" => now(), "updated_at" => now()]]);
            }

            if (isset($attributs['responsables']['organisations']) && !is_null($attributs['responsables']['organisations'])) {
                $responsables = [];

                foreach ($attributs['responsables']['organisations'] as $key => $organisation_responsable) {

                    if (!($organisation = app(OrganisationRepository::class)->findById($organisation_responsable))) throw new Exception("Organisation inconnu", 1);

                    // Add directly to the array with the expected format
                    $responsables[$organisation->id] = [
                        "responsableable_type" => Organisation::class,
                        "programmeId" => $programme->id,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }

                $indicateur->organisations_responsable()->sync($responsables);
            }

            if (isset($attributs['sites'])) {

                $sites = [];
                foreach ($attributs['sites'] as $id) {
                    if (!($site = app(SiteRepository::class)->findById($id))) throw new Exception("Site introuvable", Response::HTTP_NOT_FOUND);

                    array_push($sites, $site->id);
                }

                $indicateur->sites()->sync($sites, ["programmeId" => $attributs['programmeId']]);
            }

            $indicateur->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($indicateur));

            //LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Indicateur modifié", 'data' => new IndicateursResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addStrutureResponsable($indicateur, array $attributs): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (!is_object($indicateur) && !($indicateur = $this->repository->findById($indicateur))) throw new Exception("Indicateur inconnu", 1);


            if (isset($attributs['responsables']['ug'])) {
                $indicateur->ug_responsable()->sync([$attributs['responsables']['ug'] => ["responsableable_type" => UniteeDeGestion::class, "programmeId" => auth()->user()->programmeId, "created_at" => now(), "updated_at" => now()]]);
            }

            if (isset($attributs['responsables']['organisations'])) {
                $responsables = [];
                \Illuminate\Support\Facades\Log::notice("Log responsable organisation : " . json_encode($attributs['responsables']['organisations']));
                foreach ($attributs['responsables']['organisations'] as $key => $organisation_responsable) {

                    if (!($organisation = app(OrganisationRepository::class)->findById($organisation_responsable))) throw new Exception("Organisation inconnu", 1);

                    // Add directly to the array with the expected format
                    $responsables[$organisation->id] = [
                        "responsableable_type" => Organisation::class,
                        "programmeId" => auth()->user()->programmeId,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }

                $indicateur->organisations_responsable()->attach($responsables);
            }

            $indicateur->refresh();

            \Illuminate\Support\Facades\Log::notice("Log responsable organisation : " . json_encode($indicateur->organisations_responsable));

            DB::commit(); // ⚠️ CORRECTION PRINCIPALE : Ajout du commit manquant

            return response()->json(['statut' => 'success', 'message' => 'Structures responsables ajoutées avec succès', 'data' => new IndicateurResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addAnneesCible($indicateur, array $attributs): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (!is_object($indicateur) && !($indicateur = $this->repository->findById($indicateur))) throw new Exception("Indicateur inconnu", 1);

            $this->setIndicateurValeursCible($indicateur, auth()->user()->programme, $attributs["anneesCible"]);

            $indicateur->refresh();

            DB::commit(); // ⚠️ CORRECTION : Ajout du commit manquant

            return response()->json(['statut' => 'success', 'message' => 'Années cibles ajoutées avec succès', 'data' => new IndicateurResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * add new keys
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function addValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (!is_object($indicateurId) && !($indicateurId = $this->repository->findById($indicateurId))) throw new Exception("Indicateur inconnu", 1);

            if ($indicateurId->suivis->isNotEmpty()) throw new Exception("Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour.", 500);


            $this->attachValueKeys($indicateurId, $attributs);
            /*
            foreach ($attributs['value_keys'] as $key => $value_key) {

                $indicateurValueKey = IndicateurValueKey::find($value_key['id']);

                if (!$indicateurValueKey) {
                    throw new Exception("Cle d'indicateur inconnue.", 404);
                }

                $uniteeMesure = isset($value_key["uniteeMesureId"]) ? (optional(Unitee::find($value_key['uniteeMesureId'])) ?? $indicateurValueKey->uniteeMesure) : ($indicateurId->uniteeMesure ? $indicateurId->uniteeMesure : $indicateurValueKey->uniteeMesure);
                $indicateurId->valueKeys()->attach($indicateurValueKey->id, ["uniteeMesureId" => $uniteeMesure->id, "type" => $uniteeMesure->nom]);
            }*/

            $indicateurId->save();

            $indicateurId->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateurId));

            //LogActivity::addToLog("Ajout de cle d'indicateur", $message, get_class($indicateurId), $indicateurId->id);

            DB::commit();

            Cache::forget('indicateurs');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateurResource($indicateurId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * remove new keys
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function removeValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (!is_object($indicateurId) && !($indicateurId = $this->repository->findById($indicateurId))) throw new Exception("Indicateur inconnu", 1);

            if ($indicateurId->suivis->isNotEmpty()) throw new Exception("Cet indicateur a deja ete suivi et donc ne peut plus etre mis a jour.", 500);

            $valueKeys = [];

            foreach ($attributs['value_keys'] as $key => $value_key) {

                $indicateurValueKey = IndicateurValueKey::find($value_key);

                if (!$indicateurValueKey) {
                    throw new Exception("Cle d'indicateur inconnue.", 404);
                }

                array_push($valueKeys, $indicateurValueKey->id);
            }

            DB::table('indicateur_value_keys_mapping')
                ->where('indicateurId', $indicateurId->id)
                ->whereIn('indicateurValueKeyId', $valueKeys)
                ->update(['deleted_at' => now()]);

            $indicateurId->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateurId));

            //LogActivity::addToLog("Ajout de cle d'indicateur", $message, get_class($indicateurId), $indicateurId->id);

            DB::commit();

            Cache::forget('indicateurs');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateurResource($indicateurId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function nouvelleUniteeMesure($nom)
    {
        return $this->uniteeMesureRepository->create(["nom" => $nom]);
    }

    /**
     * Attach value keys to indicateur
     *
     * @param Indicateur $indicateur
     * @param array $attributs
     *
     * @return void
     */
    protected function attachValueKeys(Indicateur $indicateur, array $attributs)
    {

        if (isset($attributs["value_keys"])) {
            foreach ($attributs["value_keys"] as $key => $value_key) {

                $indicateurValueKey = IndicateurValueKey::find($value_key['id']);

                if (!$indicateurValueKey) {
                    throw new Exception("Cle d'indicateur inconnue.", 404);
                }

                $uniteeMesure = isset($value_key["uniteeMesureId"]) ? (optional(Unitee::find($value_key['uniteeMesureId'])) ?? $indicateurValueKey->uniteeMesure) : ($indicateur->unitee_mesure ? $indicateur->unitee_mesure : $indicateurValueKey->uniteeMesure);
                $indicateur->valueKeys()->attach($indicateurValueKey->id, ["uniteeMesureId" => $uniteeMesure->id, "type" => $uniteeMesure->nom]);
            }
        } else {

            $indicateurValueKey = IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first();

            if (!$indicateurValueKey) {
                throw new Exception("Cle d'indicateur inconnu.", 404);
            }

            if (!$indicateur->unitee_mesure) {
                if (!isset($attributs["uniteeMesureId"])) {
                    throw new Exception("Veuillez preciser l'unite de mesure de l'indicateur inconnue", 404);
                }

                if (!($unitee = Unitee::find($attributs['uniteeMesureId']))) {
                    throw new Exception("Unitee de mesure inconnue", 404);
                }
            } else {
                $unitee = $indicateur->unitee_mesure;
            }

            $indicateur->valueKeys()->attach($indicateurValueKey->id, ["uniteeMesureId" => $unitee->id, "type" => $unitee->nom]);
        }
    }

    /**
     * Set Indicateur Value
     *
     * @param Indicateur $indicateur
     * @param array|id $valeursDeBase
     * @param array $valeurDeBase
     *
     * @return array
     */
    protected function setIndicateurValue(Indicateur $indicateur, Programme $programme, $valeursDeBase, array $valeurDeBase = [])
    {

        if (is_array($valeursDeBase)) {
            foreach ($valeursDeBase as $key => $item) {
                if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $item['keyId'])->first())) {
                    $valeur = $indicateur->valeursDeBase()->create(["value" => $item["value"], "indicateurValueKeyMapId" => $key->pivot->id, 'programmeId' => $programme->id]);

                    $valeurDeBase = array_merge($valeurDeBase, ["{$key->key}" => $valeur->value]);
                }
            }
        } else {

            $mapKey = optional($indicateur->valueKey()->pivot)->id ?? (optional((IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first())->pivot)->id ?? null);

            if (is_null($mapKey)) throw new Exception("Cle d'indicateur inconnu.", 404);

            $valeur = $indicateur->valeursDeBase()->create(["value" => $valeursDeBase, "indicateurValueKeyMapId" => $mapKey, 'programmeId' => $programme->id]);

            $valeurDeBase = array_merge($valeurDeBase, ["{$indicateur->valueKey()->key}" => $valeur->value]);
        }

        return $valeurDeBase;
    }

    /**
     * Set Indicateur Value
     *
     * @param Indicateur $indicateur
     * @param array|id $valeursDeBase
     * @param array $valeurDeBase
     *
     * @return array
     */
    protected function setIndicateurValeursCible(Indicateur $indicateur, Programme $programme, $annneesCible = [])
    {
        if (is_array($annneesCible)) {
            foreach ($annneesCible as $key => $anneeCible) {

                if (!($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $indicateur->id)->where("annee", $anneeCible['annee'])->first())) {

                    if (!array_key_exists('valeurCible', $anneeCible) || !isset($anneeCible['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$anneeCible['annee']}.", 400);

                    $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($anneeCible, ["cibleable_id" => $indicateur->id, "cibleable_type" => get_class($indicateur), 'programmeId' => $programme->id]));
                    $valeurCibleIndicateur->save();
                    $valeurCibleIndicateur->refresh();

                    $valeurCible = [];

                    if ($indicateur->agreger && is_array($anneeCible["valeurCible"])) {

                        foreach ($anneeCible["valeurCible"] as $key => $data) {

                            if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first())) {
                                $valeur = $valeurCibleIndicateur->valeursCible()->create(["value" => $data["value"], "indicateurValueKeyMapId" => $key->pivot->id, 'programmeId' => $programme->id]);

                                $valeurCible = array_merge($valeurCible, ["{$key->key}" => $valeur->value]);
                            }
                        }
                    } else if (!$indicateur->agreger && !is_array($anneeCible["valeurCible"])) {
                        //dd($anneeCible["valeurCible"]);
                        $valeur = $valeurCibleIndicateur->valeursCible()->create(["value" => $anneeCible["valeurCible"], "indicateurValueKeyMapId" => $indicateur->valueKey()->pivot->id, 'programmeId' => $programme->id]);

                        $valeurCible = array_merge($valeurCible, ["{$indicateur->valueKey()->key}" => $valeur->value]);
                        //$valeurCible = ["key" => $indicateur->valueKey()->key, "value" => $valeur->value];
                    } else {
                        throw new Exception("Veuillez préciser la valeur cible dans le format adequat.", 400);
                    }

                    $valeurCibleIndicateur->valeurCible = $valeurCible;

                    $valeurCibleIndicateur->save();
                }
            }
        }
    }


    /*if (!($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $attributs['indicateurId'])->where("annee", $attributs['annee'])->first())) {

        if (!array_key_exists('valeurCible', $attributs) || !isset($attributs['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$attributs['annee']} de ce suivi.", 400);

        $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $indicateur->id, "cibleable_type" => get_class($indicateur)]));
        $valeurCibleIndicateur->save();
        $valeurCibleIndicateur->refresh();

        $valeurCible = [];

        if ($indicateur->agreger && is_array($attributs["valeurCible"])) {

            foreach ($attributs["valeurCible"] as $key => $data) {

                if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first())) {
                    $valeur = $valeurCibleIndicateur->valeursCible()->create(["value" => $data["value"], "indicateurValueKeyMapId" => $key->pivot->id]);

                    $valeurCible = array_merge($valeurCible, ["{$key->key}" => $valeur->value]);
                }
            }

        }
        else if (!$indicateur->agreger && !is_array($attributs["valeurCible"])) {
            $valeur = $valeurCibleIndicateur->valeursCible()->create(["value" => $attributs["valeurRealise"], "indicateurValueKeyMapId" => $indicateur->valueKey()->pivot->id]);

            $valeurCible = array_merge($valeurCible, ["{$indicateur->valueKey()->key}" => $valeur->value]);
            //$valeurCible = ["key" => $indicateur->valueKey()->key, "value" => $valeur->value];
        }
        else{
            throw new Exception("Veuillez préciser la valeur cible dans le format adequat.", 400);
        }

        $valeurCibleIndicateur->valeurCible = $valeurCible;

        //$valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $attributs['indicateurId'], "cibleable_type" => "App\\Models\\Indicateur"]));

        $valeurCibleIndicateur->save();
    }*/



    /**
     * Modifie les valeurs cibles d'un indicateur
     * Gère les indicateurs agrégés et non agrégés avec leurs clés de valeurs
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param array $attributs Données des valeurs cibles à modifier
     * @return JsonResponse
     */
    public function updateValeursCibles($indicateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification que l'utilisateur a les droits de modification
            $programme = Auth::user()->programme;

            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            // Gérer le changement de type d'indicateur si nécessaire
            if (isset($attributs['agreger']) && $indicateur->agreger !== (bool)$attributs['agreger']) {
                // Déléguer le changement de type à la fonction spécialisée
                $changeTypeResult = $this->changeIndicateurType($indicateur, $attributs);

                // Si le changement a échoué, retourner l'erreur
                if ($changeTypeResult->getStatusCode() !== 200) {
                    return $changeTypeResult;
                }

                // Recharger l'indicateur après le changement de type
                $indicateur->refresh();
            }

            // Validation des données d'entrée
            if (!isset($attributs['anneesCible']) || !is_array($attributs['anneesCible'])) {
                throw new Exception("Les années cibles doivent être fournies sous forme de tableau", 422);
            }

            // Si anneesCible est un tableau vide, supprimer toutes les valeurs cibles
            if (empty($attributs['anneesCible'])) {
                // Récupérer toutes les valeurs cibles de cet indicateur
                $valeursCiblesIndicateur = $this->valeurCibleIndicateurRepository
                    ->newInstance()
                    ->where("cibleable_id", $indicateur->id)
                    ->where("cibleable_type", get_class($indicateur))
                    ->get();

                // Supprimer toutes les valeurs cibles et leurs entrées associées
                foreach ($valeursCiblesIndicateur as $valeurCibleIndicateur) {
                    $valeurCibleIndicateur->valeursCible()->delete();
                    $valeurCibleIndicateur->delete();
                }

                // Rafraîchissement de l'indicateur
                $indicateur->refresh();

                // Logging de l'activité
                $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
                $message = Str::ucfirst($acteur) . " a supprimé toutes les valeurs cibles de l'indicateur " . $indicateur->nom;

                DB::commit();

                // Nettoyage du cache
                Cache::forget('indicateurs');
                Cache::forget('indicateurs-' . $indicateur->id);

                return response()->json([
                    'statut' => 'success',
                    'message' => 'Toutes les valeurs cibles ont été supprimées avec succès',
                    'data' => new IndicateursResource($indicateur),
                    'statutCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }

            // Traitement de chaque année cible
            foreach ($attributs['anneesCible'] as $anneeCible) {

                // Validation des données de l'année
                if (!isset($anneeCible['annee'])) {
                    throw new Exception("L'année doit être spécifiée pour chaque valeur cible", 422);
                }

                if (!isset($anneeCible['valeurCible'])) {
                    throw new Exception("La valeur cible doit être spécifiée pour l'année {$anneeCible['annee']}", 422);
                }

                // Validation de l'année dans la période du programme
                $annee = (int)$anneeCible['annee'];
                $anneeDebut = Carbon::parse($programme->debut)->year;
                $anneeFin = Carbon::parse($programme->fin)->year;

                if ($annee < $anneeDebut || $annee > $anneeFin) {
                    throw new Exception("L'année {$annee} doit être comprise entre {$anneeDebut} et {$anneeFin}", 422);
                }

                // Recherche ou création de la valeur cible pour cette année
                $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository
                    ->newInstance()
                    ->where("cibleable_id", $indicateur->id)
                    ->where("cibleable_type", get_class($indicateur))
                    ->where("annee", $annee)
                    ->first();

                // Si la valeur cible n'existe pas, on la crée
                if (!$valeurCibleIndicateur) {
                    $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->create([
                        "annee" => $annee,
                        "cibleable_id" => $indicateur->id,
                        "cibleable_type" => get_class($indicateur),
                        "programmeId" => $programme->id,
                        "valeurCible" => [] // Sera mis à jour ci-dessous
                    ]);
                }

                // Gestion selon le type d'indicateur (agrégé ou simple)
                $valeurCible = [];

                if ($indicateur->agreger) {
                    // Indicateur agrégé - les valeurs sont un tableau avec des clés
                    if (!is_array($anneeCible["valeurCible"])) {
                        throw new Exception("Pour un indicateur agrégé, les valeurs cibles doivent être un tableau avec les clés correspondantes pour l'année {$annee}", 422);
                    }

                    // Validation que toutes les clés de l'indicateur ont une valeur
                    $indicateurKeys = $indicateur->valueKeys->pluck('id')->toArray();
                    $valeursKeys = collect($anneeCible["valeurCible"])->pluck('keyId')->toArray();

                    $missingKeys = array_diff($indicateurKeys, $valeursKeys);
                    if (!empty($missingKeys)) {
                        throw new Exception("Les clés d'indicateur suivantes sont manquantes dans les valeurs cibles pour l'année {$annee}: " . implode(', ', $missingKeys), 422);
                    }

                    // Suppression des anciennes valeurs pour cette année
                    $valeurCibleIndicateur->valeursCible()->delete();

                    // Création des nouvelles valeurs
                    foreach ($anneeCible["valeurCible"] as $data) {
                        if (!isset($data['keyId']) || !isset($data['value'])) {
                            throw new Exception("Chaque valeur cible doit contenir 'keyId' et 'value' pour l'année {$annee}", 422);
                        }

                        // Vérification que la clé existe dans l'indicateur
                        $valueKey = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first();

                        if (!$valueKey) {
                            throw new Exception("La clé {$data['keyId']} n'est pas associée à cet indicateur", 422);
                        }

                        // Validation que la valeur est numérique si l'unité de mesure l'exige
                        if ($valueKey->pivot->type !== 'text' && !is_numeric($data['value'])) {
                            throw new Exception("La valeur pour la clé '{$valueKey->key}' doit être numérique pour l'année {$annee}", 422);
                        }

                        // Création de la valeur cible
                        $valeur = $valeurCibleIndicateur->valeursCible()->create([
                            "value" => $data["value"],
                            "indicateurValueKeyMapId" => $valueKey->pivot->id,
                            "programmeId" => $programme->id
                        ]);

                        $valeurCible["{$valueKey->key}"] = $valeur->value;
                    }
                } else {
                    // Indicateur simple - une seule valeur
                    if (is_array($anneeCible["valeurCible"])) {
                        throw new Exception("Pour un indicateur simple, la valeur cible doit être une valeur unique pour l'année {$annee}", 422);
                    }

                    // Validation que la valeur est numérique si nécessaire
                    $valueKey = $indicateur->valueKey();
                    if (!$valueKey) {
                        throw new Exception("Aucune clé de valeur trouvée pour cet indicateur", 500);
                    }

                    if ($valueKey->pivot->type !== 'text' && !is_numeric($anneeCible["valeurCible"])) {
                        throw new Exception("La valeur cible doit être numérique pour l'année {$annee}", 422);
                    }

                    // Suppression de l'ancienne valeur
                    $valeurCibleIndicateur->valeursCible()->delete();

                    // Création de la nouvelle valeur
                    $valeur = $valeurCibleIndicateur->valeursCible()->create([
                        "value" => $anneeCible["valeurCible"],
                        "indicateurValueKeyMapId" => $valueKey->pivot->id,
                        "programmeId" => $programme->id
                    ]);

                    $valeurCible["{$valueKey->key}"] = $valeur->value;
                }

                // Mise à jour de la valeur cible consolidée
                $valeurCibleIndicateur->valeurCible = $valeurCible;
                $valeurCibleIndicateur->save();
            }

            // Rafraîchissement de l'indicateur pour obtenir les nouvelles données
            $indicateur->refresh();

            // Logging de l'activité
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié les valeurs cibles de l'indicateur " . $indicateur->nom;

            // LogActivity::addToLog("Modification valeurs cibles", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => 'Valeurs cibles mises à jour avec succès',
                'data' => new IndicateursResource($indicateur),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modifie une valeur cible spécifique pour une année donnée
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param int $annee Année de la valeur cible
     * @param array $valeurCible Nouvelle valeur cible
     * @return JsonResponse
     */
    public function updateValeurCibleAnnee($indicateur, int $annee, $valeurCible): JsonResponse
    {
        return $this->updateValeursCibles($indicateur, [
            'anneesCible' => [
                [
                    'annee' => $annee,
                    'valeurCible' => $valeurCible
                ]
            ]
        ]);
    }

    /**
     * Supprime une valeur cible pour une année donnée
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param int $annee Année de la valeur cible à supprimer
     * @return JsonResponse
     */
    public function deleteValeurCibleAnnee($indicateur, int $annee): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification des droits
            $programme = Auth::user()->programme;
            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            // Recherche de la valeur cible
            $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository
                ->newInstance()
                ->where("cibleable_id", $indicateur->id)
                ->where("cibleable_type", get_class($indicateur))
                ->where("annee", $annee)
                ->first();

            if (!$valeurCibleIndicateur) {
                throw new Exception("Aucune valeur cible trouvée pour l'année {$annee}", 404);
            }

            // Vérification qu'il n'y a pas de suivis associés
            if ($valeurCibleIndicateur->suivisIndicateur()->count() > 0) {
                throw new Exception("Impossible de supprimer cette valeur cible car des suivis y sont associés", 422);
            }

            // Suppression des valeurs détaillées et de la valeur cible
            $valeurCibleIndicateur->valeursCible()->delete();
            $valeurCibleIndicateur->delete();

            // Logging
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a supprimé la valeur cible de l'année {$annee} pour l'indicateur " . $indicateur->nom;

            // LogActivity::addToLog("Suppression valeur cible", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => "Valeur cible de l'année {$annee} supprimée avec succès",
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change le type d'indicateur (agrégé ↔ simple) et restructure les valeurs cibles
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param array $attributs Données incluant le nouveau statut agrégé et les value_keys
     * @return JsonResponse
     */
    public function changeIndicateurType($indicateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification des droits
            $programme = Auth::user()->programme;
            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            // Vérification qu'il n'y a pas de suivis
            if ($indicateur->suivisIndicateur()->isNotEmpty()) {
                throw new Exception("Impossible de changer le type d'un indicateur qui a déjà des suivis", 422);
            }

            $nouveauAgreger = isset($attributs['agreger']) ? (bool)$attributs['agreger'] : $indicateur->agreger;

            // Si le type ne change pas, pas besoin de restructurer
            if ($indicateur->agreger === $nouveauAgreger) {
                return response()->json([
                    'statut' => 'success',
                    'message' => 'Aucun changement de type nécessaire',
                    'data' => new IndicateursResource($indicateur),
                    'statutCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }

            $ancienAgreger = $indicateur->agreger;

            // 1. Sauvegarder les anciennes valeurs cibles pour conversion
            $anciensValeursCibles = $indicateur->valeursCible()->with('valeursCible')->get();

            // 2. Mettre à jour le statut agrégé
            $indicateur->agreger = $nouveauAgreger;

            // 3. Gérer les value keys selon le nouveau type
            if ($nouveauAgreger && isset($attributs['value_keys'])) {
                // Passage de simple à agrégé : ajouter les nouvelles clés
                $this->attachValueKeys($indicateur, $attributs);
            } elseif (!$nouveauAgreger) {
                // Passage d'agrégé à simple : garder seulement une clé (généralement 'moy')
                $indicateurValueKey = IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first();

                if (!$indicateurValueKey) {
                    throw new Exception("Aucune clé d'indicateur disponible", 500);
                }

                // Supprimer les anciennes associations
                DB::table('indicateur_value_keys_mapping')
                    ->where('indicateurId', $indicateur->id)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);

                // Ajouter la clé unique
                $unitee = $indicateur->unitee_mesure ?? Unitee::find($attributs['uniteeMesureId'] ?? null);
                if (!$unitee) {
                    throw new Exception("Unité de mesure requise pour indicateur simple", 422);
                }

                // Utiliser une insertion directe pour éviter les conflits avec la relation
                DB::table('indicateur_value_keys_mapping')->insert([
                    'indicateurId' => $indicateur->id,
                    'indicateurValueKeyId' => $indicateurValueKey->id,
                    'uniteeMesureId' => $unitee->id,
                    'type' => $unitee->nom,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null
                ]);
            }

            // 4. Restructurer les valeurs cibles existantes
            if ($anciensValeursCibles->isNotEmpty()) {
                $this->restructurerValeursCibles($indicateur, $anciensValeursCibles, $ancienAgreger, $nouveauAgreger, $programme);
            }

            // 5. Restructurer les valeurs de base
            $this->restructurerValeursDeBase($indicateur, $ancienAgreger, $nouveauAgreger, $programme);

            $indicateur->save();
            $indicateur->refresh();

            // Logging
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $typeText = $nouveauAgreger ? 'agrégé' : 'simple';
            $message = Str::ucfirst($acteur) . " a changé l'indicateur '{$indicateur->nom}' vers le type {$typeText}";

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => "Type d'indicateur modifié avec succès vers " . $typeText,
                'data' => new IndicateursResource($indicateur),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restructure les valeurs cibles lors du changement de type d'indicateur
     *
     * @param Indicateur $indicateur
     * @param Collection $anciensValeursCibles
     * @param bool $ancienAgreger
     * @param bool $nouveauAgreger
     * @param Programme $programme
     */
    protected function restructurerValeursCibles($indicateur, $anciensValeursCibles, $ancienAgreger, $nouveauAgreger, $programme)
    {
        foreach ($anciensValeursCibles as $ancienneValeurCible) {
            // Supprimer l'ancienne structure
            $ancienneValeurCible->valeursCible()->delete();

            $nouvelleValeurCible = [];

            if ($ancienAgreger && !$nouveauAgreger) {
                // Agrégé → Simple : prendre la somme ou la moyenne des valeurs
                $anciensValues = $ancienneValeurCible->valeurCible ?? [];
                $valeurConvertie = is_array($anciensValues) ? array_sum($anciensValues) : 0;

                $valueKey = $indicateur->valueKey();
                if ($valueKey) {
                    $valeur = $ancienneValeurCible->valeursCible()->create([
                        "value" => $valeurConvertie,
                        "indicateurValueKeyMapId" => $valueKey->pivot->id,
                        "programmeId" => $programme->id
                    ]);

                    $nouvelleValeurCible["{$valueKey->key}"] = $valeur->value;
                }

            } elseif (!$ancienAgreger && $nouveauAgreger) {
                // Simple → Agrégé : répartir la valeur sur les nouvelles clés
                $ancienneValeur = $ancienneValeurCible->valeurCible ?? [];
                $valeurUnique = is_array($ancienneValeur) ? array_values($ancienneValeur)[0] ?? 0 : 0;

                $valueKeys = $indicateur->valueKeys;
                $nombreCles = $valueKeys->count();

                if ($nombreCles > 0) {
                    $valeurParCle = $nombreCles > 1 ? $valeurUnique / $nombreCles : $valeurUnique;

                    foreach ($valueKeys as $valueKey) {
                        $valeur = $ancienneValeurCible->valeursCible()->create([
                            "value" => $valeurParCle,
                            "indicateurValueKeyMapId" => $valueKey->pivot->id,
                            "programmeId" => $programme->id
                        ]);

                        $nouvelleValeurCible["{$valueKey->key}"] = $valeur->value;
                    }
                }
            }

            // Mettre à jour la valeur cible consolidée
            $ancienneValeurCible->valeurCible = $nouvelleValeurCible;
            $ancienneValeurCible->save();
        }
    }

    /**
     * Restructure les valeurs de base lors du changement de type d'indicateur
     *
     * @param Indicateur $indicateur
     * @param bool $ancienAgreger
     * @param bool $nouveauAgreger
     * @param Programme $programme
     */
    protected function restructurerValeursDeBase($indicateur, $ancienAgreger, $nouveauAgreger, $programme)
    {
        $anciensValeursDeBase = $indicateur->valeursDeBase;

        if ($anciensValeursDeBase->isEmpty()) {
            return;
        }

        // Supprimer les anciennes valeurs de base
        $anciensValeursDeBase->each->delete();

        $nouvelleValeurDeBase = [];

        if ($ancienAgreger && !$nouveauAgreger) {
            // Agrégé → Simple : prendre la somme des valeurs de base
            $anciennesValues = $indicateur->valeurDeBase ?? [];
            $valeurConvertie = is_array($anciennesValues) ? array_sum($anciennesValues) : 0;

            $valueKey = $indicateur->valueKey();
            if ($valueKey) {
                $valeur = $indicateur->valeursDeBase()->create([
                    "value" => $valeurConvertie,
                    "indicateurValueKeyMapId" => $valueKey->pivot->id,
                    "programmeId" => $programme->id
                ]);

                $nouvelleValeurDeBase["{$valueKey->key}"] = $valeur->value;
            }

        } elseif (!$ancienAgreger && $nouveauAgreger) {
            // Simple → Agrégé : répartir la valeur sur les nouvelles clés
            $ancienneValeur = $indicateur->valeurDeBase ?? [];
            $valeurUnique = is_array($ancienneValeur) ? array_values($ancienneValeur)[0] ?? 0 : 0;

            $valueKeys = $indicateur->valueKeys;
            $nombreCles = $valueKeys->count();

            if ($nombreCles > 0) {
                $valeurParCle = $nombreCles > 1 ? $valeurUnique / $nombreCles : $valeurUnique;

                foreach ($valueKeys as $valueKey) {
                    $valeur = $indicateur->valeursDeBase()->create([
                        "value" => $valeurParCle,
                        "indicateurValueKeyMapId" => $valueKey->pivot->id,
                        "programmeId" => $programme->id
                    ]);

                    $nouvelleValeurDeBase["{$valueKey->key}"] = $valeur->value;
                }
            }
        }

        // Mettre à jour la valeur de base consolidée
        $indicateur->valeurDeBase = $nouvelleValeurDeBase;
    }

    /**
     * Modifie la valeur de base d'un indicateur
     * Gère les indicateurs agrégés et non agrégés avec leurs clés de valeurs
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param array $attributs Données de la nouvelle valeur de base
     * @return JsonResponse
     */
    public function updateValeurDeBase($indicateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification des droits
            $programme = Auth::user()->programme;
            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            // Vérification qu'il n'y a pas de suivis (optionnel selon les règles métier)
            //if ($indicateur->suivis->isNotEmpty()) {
            if ($indicateur->suivisIndicateur()->isNotEmpty()) {
                throw new Exception("Impossible de modifier la valeur de base d'un indicateur qui a déjà des suivis.", 422);
            }

            // Validation des données d'entrée
            if (!isset($attributs['valeurDeBase'])) {
                throw new Exception("La valeur de base doit être fournie", 422);
            }

            $nouvelleValeurDeBase = $attributs['valeurDeBase'];



            // Si anneesCible est un tableau vide, supprimer toutes les valeurs cibles
            if (empty($nouvelleValeurDeBase)) {
                // Récupérer toutes les valeurs de base de cet indicateur
                $indicateur->valeursDeBase()->delete();

                // Rafraîchissement de l'indicateur
                $indicateur->refresh();

                // Logging de l'activité
                $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
                $message = Str::ucfirst($acteur) . " a supprimé toutes les valeurs de base de l'indicateur " . $indicateur->nom;

                DB::commit();

                // Nettoyage du cache
                Cache::forget('indicateurs');
                Cache::forget('indicateurs-' . $indicateur->id);

                return response()->json([
                    'statut' => 'success',
                    'message' => 'Toutes les valeurs de base ont été supprimées avec succès',
                    'data' => new IndicateursResource($indicateur),
                    'statutCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }

            // Validation selon le type d'indicateur
            if ($indicateur->agreger) {
                // Indicateur agrégé - les valeurs sont un tableau avec des clés
                if (!is_array($nouvelleValeurDeBase)) {
                    throw new Exception("Pour un indicateur agrégé, la valeur de base doit être un tableau avec les clés correspondantes", 422);
                }

                // Validation que toutes les clés de l'indicateur ont une valeur
                $indicateurKeys = $indicateur->valueKeys->pluck('id')->toArray();
                $valeursKeys = collect($nouvelleValeurDeBase)->pluck('keyId')->toArray();

                $missingKeys = array_diff($indicateurKeys, $valeursKeys);
                if (!empty($missingKeys)) {
                    throw new Exception("Les clés d'indicateur suivantes sont manquantes dans la valeur de base: " . implode(', ', $missingKeys), 422);
                }

                // Suppression des anciennes valeurs de base
                $indicateur->valeursDeBase()->delete();

                $valeurDeBase = [];

                // Création des nouvelles valeurs de base
                foreach ($nouvelleValeurDeBase as $data) {
                    if (!isset($data['keyId']) || !isset($data['value'])) {
                        throw new Exception("Chaque valeur de base doit contenir 'keyId' et 'value'", 422);
                    }

                    // Vérification que la clé existe dans l'indicateur
                    $valueKey = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first();

                    if (!$valueKey) {
                        throw new Exception("La clé {$data['keyId']} n'est pas associée à cet indicateur", 422);
                    }

                    // Validation que la valeur est numérique si l'unité de mesure l'exige
                    if ($valueKey->type !== 'text' && !is_numeric($data['value'])) {
                        throw new Exception("La valeur pour la clé '{$valueKey->key}' doit être numérique", 422);
                    }

                    // Création de la valeur de base
                    $valeur = $indicateur->valeursDeBase()->create([
                        "value" => $data["value"],
                        "indicateurValueKeyMapId" => $valueKey->pivot->id,
                        "programmeId" => $programme->id
                    ]);

                    $valeurDeBase["{$valueKey->key}"] = $valeur->value;
                }

            } else {
                // Indicateur simple - une seule valeur
                if (is_array($nouvelleValeurDeBase)) {
                    throw new Exception("Pour un indicateur simple, la valeur de base doit être une valeur unique", 422);
                }

                // Validation que la valeur est numérique si nécessaire
                $valueKey = $indicateur->valueKey();

                // Recuperer la cle moy si ca existe sinon cree
                /**
                 * Recuperer la cle moy si ca existe sinon cree
                 *  $unite = Unitee::firstOrCreate(["type" => nombre],["nom" => Nombre,"type" => nombre, 'programmeId' => $indicateur->programmeId])
                 * array('libelle' => "Moyenne", 'key' => moy, 'type' => $unite->type, 'description', 'uniteeMesureId' => $unite->id, 'programmeId'  => $indicateur->programmeId);
                 */

                $valueKey = IndicateurValueKey::where('key', 'moy')->first() ?? IndicateurValueKey::first();

                /* $unite = Unitee::firstOrCreate(
                    ["type" => "nombre"], // condition
                    ["nom" => "Nombre", "type" => "nombre", "programmeId" => $indicateur->programmeId] // valeurs si création
                );

                $valueKey = IndicateurValueKey::firstOrCreate(
                    ["key" => "moy"], // condition
                    [
                        "libelle"        => "Moyenne",
                        "key"            => "moy",
                        "type"           => $unite->type,
                        "description"    => "Clé générée automatiquement pour la moyenne",
                        "uniteeMesureId" => $unite->id,
                        "programmeId"    => $indicateur->programmeId,
                    ] // valeurs si création
                ); */

                if (!$valueKey) {
                    throw new Exception("Aucune clé de valeur trouvée pour cet indicateur", 500);
                }

                if ($valueKey->pivot->type !== 'text' && !is_numeric($nouvelleValeurDeBase)) {
                    throw new Exception("La valeur de base doit être numérique", 422);
                }

                // Suppression de l'ancienne valeur de base
                $indicateur->valeursDeBase()->delete();

                // Création de la nouvelle valeur de base
                $valeur = $indicateur->valeursDeBase()->create([
                    "value" => $nouvelleValeurDeBase,
                    "indicateurValueKeyMapId" => $valueKey->pivot->id,
                    "programmeId" => $programme->id
                ]);

                $valeurDeBase["{$valueKey->key}"] = $valeur->value;
            }

            // Mise à jour de la valeur de base consolidée
            $indicateur->valeurDeBase = $valeurDeBase;
            $indicateur->save();

            // Rafraîchissement de l'indicateur
            $indicateur->refresh();

            // Logging de l'activité
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié la valeur de base de l'indicateur " . $indicateur->nom;

            // LogActivity::addToLog("Modification valeur de base", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => 'Valeur de base mise à jour avec succès',
                'data' => new IndicateursResource($indicateur),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modifie complètement un indicateur avec valeurs de base, cibles et type
     * Fonction complète qui peut changer le type d'indicateur et toutes ses valeurs
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param array $attributs Données complètes de l'indicateur
     * @return JsonResponse
     */
    public function updateIndicateurComplet($indicateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification des droits
            $programme = Auth::user()->programme;
            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            // Gestion du changement de type d'indicateur si nécessaire
            if (isset($attributs['agreger']) && $indicateur->agreger !== (bool)$attributs['agreger']) {
                $changeTypeResult = $this->changeIndicateurType($indicateur, $attributs);

                if ($changeTypeResult->getStatusCode() !== 200) {
                    return $changeTypeResult;
                }

                $indicateur->refresh();
            }

            // Modification des attributs de base de l'indicateur
            $champsModifiables = [
                'nom', 'description', 'type_de_variable', 'hypothese', 'indice',
                'uniteeMesureId', 'categorieId', 'methode_de_la_collecte',
                'frequence_de_la_collecte', 'sources_de_donnee'
            ];

            foreach ($champsModifiables as $champ) {
                if (isset($attributs[$champ])) {
                    $indicateur->$champ = $attributs[$champ];
                }
            }

            // Modification de la valeur de base si fournie
            if (isset($attributs['valeurDeBase'])) {
                $resultValeurBase = $this->updateValeurDeBase($indicateur, $attributs);
                if ($resultValeurBase->getStatusCode() !== 200) {
                    return $resultValeurBase;
                }
                $indicateur->refresh();
            }

            // Modification des valeurs cibles si fournies
            if (isset($attributs['anneesCible'])) {
                $resultValeursCibles = $this->updateValeursCibles($indicateur, $attributs);
                if ($resultValeursCibles->getStatusCode() !== 200) {
                    return $resultValeursCibles;
                }
                $indicateur->refresh();
            }

            // Modification des responsables si fournies
            if (isset($attributs['responsables'])) {
                if (isset($attributs['responsables']['ug'])) {
                    $indicateur->ug_responsable()->sync([
                        $attributs['responsables']['ug'] => [
                            "responsableable_type" => UniteeDeGestion::class,
                            "programmeId" => $programme->id,
                            "created_at" => now(),
                            "updated_at" => now()
                        ]
                    ]);
                }

                if (isset($attributs['responsables']['organisations'])) {
                    $responsables = [];
                    foreach ($attributs['responsables']['organisations'] as $organisation_responsable) {
                        if (!($organisation = app(OrganisationRepository::class)->findById($organisation_responsable))) {
                            throw new Exception("Organisation inconnue", 404);
                        }

                        $responsables[$organisation->id] = [
                            "responsableable_type" => Organisation::class,
                            "programmeId" => $programme->id,
                            "created_at" => now(),
                            "updated_at" => now()
                        ];
                    }
                    $indicateur->organisations_responsable()->sync($responsables);
                }
            }

            // Modification des sites si fournis
            if (isset($attributs['sites'])) {
                $sites = [];
                foreach ($attributs['sites'] as $id) {
                    if (!($site = app(SiteRepository::class)->findById($id))) {
                        throw new Exception("Site introuvable", 404);
                    }
                    array_push($sites, $site->id);
                }
                $indicateur->sites()->sync($sites, ["programmeId" => $programme->id]);
            }

            $indicateur->save();
            $indicateur->refresh();

            // Logging
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié complètement l'indicateur " . $indicateur->nom;

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => 'Indicateur modifié avec succès',
                'data' => new IndicateursResource($indicateur),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modifie complètement un indicateur avec validation groupée des erreurs.
     * Fonction complète qui peut changer le type d'indicateur et toutes ses valeurs.
     *
     * @param mixed $indicateur ID ou instance de l'indicateur
     * @param array $attributs Données complètes de l'indicateur
     * @return JsonResponse
     */
    public function updateIndicateurCompletAvecValidation($indicateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Récupération de l'indicateur
            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            }

            if (!$indicateur) {
                throw new Exception("Indicateur inconnu", 404);
            }

            // Vérification des droits
            $programme = Auth::user()->programme;
            if ($indicateur->programmeId !== $programme->id) {
                throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
            }

            $errors = [];

            // Gestion du changement de type d'indicateur si nécessaire
            if (isset($attributs['agreger']) && $indicateur->agreger !== (bool)$attributs['agreger']) {
                $changeTypeResult = $this->changeIndicateurType($indicateur, $attributs);

                if ($changeTypeResult->getStatusCode() !== 200) {
                    // Un échec ici est structurel et bloquant, on arrête tout.
                    DB::rollBack();
                    return $changeTypeResult;
                }

                $indicateur->refresh();
            }

            // Modification des attributs de base de l'indicateur
            $champsModifiables = [
                'nom', 'description', 'type_de_variable', 'hypothese', 'indice',
                'uniteeMesureId', 'categorieId', 'methode_de_la_collecte',
                'frequence_de_la_collecte', 'sources_de_donnee'
            ];

            foreach ($champsModifiables as $champ) {
                if (isset($attributs[$champ])) {
                    $indicateur->$champ = $attributs[$champ];
                }
            }

            // Modification de la valeur de base si fournie
            if (isset($attributs['valeurDeBase'])) {
                $resultValeurBase = $this->updateValeurDeBase($indicateur, $attributs);
                if ($resultValeurBase->getStatusCode() !== 200) {
                    $errors['valeurDeBase'] = json_decode($resultValeurBase->getContent())->message;
                } else {
                    $indicateur->refresh();
                }
            }

            // Modification des valeurs cibles si fournies
            if (isset($attributs['anneesCible'])) {
                $resultValeursCibles = $this->updateValeursCibles($indicateur, $attributs);
                if ($resultValeursCibles->getStatusCode() !== 200) {
                    $errors['anneesCible'] = json_decode($resultValeursCibles->getContent())->message;
                } else {
                    $indicateur->refresh();
                }
            }

            // Si des erreurs ont été collectées, on annule tout et on les retourne
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Plusieurs erreurs de validation sont survenues.',
                    'errors' => $errors,
                    'statutCode' => Response::HTTP_UNPROCESSABLE_ENTITY
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Modification des responsables si fournies
            if (isset($attributs['responsables'])) {
                if (isset($attributs['responsables']['ug'])) {
                    $indicateur->ug_responsable()->sync([
                        $attributs['responsables']['ug'] => [
                            "responsableable_type" => UniteeDeGestion::class,
                            "programmeId" => $programme->id,
                            "created_at" => now(),
                            "updated_at" => now()
                        ]
                    ]);
                }

                if (isset($attributs['responsables']['organisations'])) {
                    $responsables = [];
                    foreach ($attributs['responsables']['organisations'] as $organisation_responsable) {
                        if (!($organisation = app(OrganisationRepository::class)->findById($organisation_responsable))) {
                            throw new Exception("Organisation inconnue", 404);
                        }

                        $responsables[$organisation->id] = [
                            "responsableable_type" => Organisation::class,
                            "programmeId" => $programme->id,
                            "created_at" => now(),
                            "updated_at" => now()
                        ];
                    }
                    $indicateur->organisations_responsable()->sync($responsables);
                }
            }

            // Modification des sites si fournis
            if (isset($attributs['sites'])) {
                $sites = [];
                foreach ($attributs['sites'] as $id) {
                    if (!($site = app(SiteRepository::class)->findById($id))) {
                        throw new Exception("Site introuvable", 404);
                    }
                    array_push($sites, $site->id);
                }
                $indicateur->sites()->sync($sites, ["programmeId" => $programme->id]);
            }

            $indicateur->save();
            $indicateur->refresh();

            // Logging
            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié complètement l'indicateur " . $indicateur->nom;

            DB::commit();

            // Nettoyage du cache
            Cache::forget('indicateurs');
            Cache::forget('indicateurs-' . $indicateur->id);

            return response()->json([
                'statut' => 'success',
                'message' => 'Indicateur modifié avec succès',
                'data' => new IndicateursResource($indicateur),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
