<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\suivi\SuiviIndicateursResource;
use App\Http\Resources\suivi\SuivisIndicateurResource;
use App\Models\Indicateur;
use App\Models\Organisation;
use App\Models\SuiviIndicateur;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\CommentaireNotification;
use App\Notifications\SuiviIndicateurNotification;
use App\Repositories\IndicateurRepository;
use App\Repositories\SuiviIndicateurRepository;
use App\Repositories\ValeurCibleIndicateurRepository;
use App\Traits\Helpers\HelperTrait;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SuiviIndicateurServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Interface SuiviIndicateurServiceInterface
 * @package Core\Services\Interfaces
 */
class SuiviIndicateurService extends BaseService implements SuiviIndicateurServiceInterface
{

    use HelperTrait;
    /**
     * @var repository
     */
    protected $valeurCibleIndicateurRepository;
    protected $repository;
    protected $indicateurRepository;

    /**
     * SuiviIndicateurRepository constructor.
     *
     * @param SuiviIndicateurRepository $suiviIndicateurRepository
     */
    public function __construct(SuiviIndicateurRepository $suiviIndicateurRepository, IndicateurRepository $indicateurRepository, ValeurCibleIndicateurRepository $valeurCibleIndicateurRepository)
    {
        parent::__construct($suiviIndicateurRepository);
        $this->repository = $suiviIndicateurRepository;
        $this->indicateurRepository = $indicateurRepository;
        $this->valeurCibleIndicateurRepository = $valeurCibleIndicateurRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $suivis_indicateurs = [];

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $suivis_indicateurs = Auth::user()->profilable->suivis_indicateurs;
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $suivis_indicateurs = Auth::user()->programme->suivis_indicateurs;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisIndicateurResource::collection($suivis_indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisIndicateurResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
     * Filtre suivi
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function filter($attributs): JsonResponse
    {

        try {

            $suivisIndicateur = SuiviIndicateur::when((isset($attributs['dateSuivie']) && $attributs['dateSuivie']), function($query) use($attributs) {
                $query->where('dateSuivie', $attributs['dateSuivie']);
            })->when((isset($attributs['annee']) && $attributs['annee']), function($query) use($attributs) {
                $query->whereRaw('YEAR(dateSuivie) = ?', [$attributs['annee']]);
            })->when((isset($attributs['trimestre']) && $attributs['trimestre']), function($query) use($attributs) {
                $query->where('trimestre', $attributs['trimestre']);
            })->get();

            if (isset($attributs['date_debut']) && $attributs['date_debut'] != null) {
                $suivisIndicateur = $suivisIndicateur->filter(function ($suiviIndicateur) use ($attributs) {
                    return Carbon::parse($suiviIndicateur->created_at)->format("Y-m-d") >= Carbon::parse($attributs['date_debut'])->format("Y-m-d");
                });
            }

            if (isset($attributs['date_fin']) && $attributs['date_fin'] != null) {
                $suivisIndicateur = $suivisIndicateur->filter(function ($suiviIndicateur) use ($attributs) {
                    return Carbon::parse($suiviIndicateur->created_at)->format("Y-m-d") <= Carbon::parse($attributs['date_fin'])->format("Y-m-d");
                });
            }

            if (isset($attributs['indicateurId']) && $attributs['indicateurId'] != null) {
                $suivisIndicateur = $suivisIndicateur->filter(function ($suiviIndicateur) use ($attributs) {
                    return $suiviIndicateur->valeurCible->where(["cibleable_type" => "App\Models\Indicateur", "cibleable_id" => $attributs['indicateurId']])->count();
                })->where('dateSuivie', $attributs['dateSuivie']);
            }

            if (isset($attributs['bailleurId']) && $attributs['bailleurId'] != null) {
                $suivisIndicateur = $suivisIndicateur->map(function ($suiviIndicateur) use ($attributs) {
                    return $suiviIndicateur->valeurCible->cibleable->where("bailleurId", $attributs['bailleurId']) ? $suiviIndicateur : null;
                })->where('dateSuivie', $attributs['dateSuivie']);
            }


            if (isset($attributs['categorieId']) && $attributs['categorieId'] != null) {
                $suivisIndicateur = $suivisIndicateur->map(function ($suiviIndicateur) use ($attributs) {
                    return $suiviIndicateur->valeurCible->cibleable->where("categorieId", $attributs['categorieId']) ? $suiviIndicateur : null;
                })->where('dateSuivie', $attributs['dateSuivie']);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisIndicateurResource::collection($suivisIndicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dateSuivie($attributs): JsonResponse
    {

        try {

            $date = SuiviIndicateur::where('trimestre', $attributs['trimestre'])->where('dateSuivie', '>=', $attributs['annee'] . "-01-01")->where('dateSuivie', '<=', $attributs['annee'] . "-12-31")->pluck('dateSuivie');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $date, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Suivi trimestriel d'un indicateur
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function create($attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (array_key_exists('indicateurId', $attributs) && !($indicateur = $this->indicateurRepository->findById($attributs['indicateurId']))) throw new Exception("Catégorie inconnue", 404);

            if (!($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $attributs['indicateurId'])->where("annee", $attributs['annee'])->first())) {

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
            }

            if (!array_key_exists('dateSuivie', $attributs)) {

                switch ($attributs['trimestre']) {
                    case 1:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-03-31 " . date('h:i:s')]);
                        break;

                    case 2:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-06-30 " . date('h:i:s')]);
                        break;

                    case 3:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-09-30 " . date('h:i:s')]);
                        break;

                    case 4:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-12-31 " . date('h:i:s')]);
                        break;

                    default:
                        # code...
                        break;
                }
            }

            $indicateur = Indicateur::find($attributs['indicateurId']);

            /*if($indicateur->unitee_mesure->type && !ctype_digit($attributs['valeurRealise']) )
            {
                throw new Exception("La valeur realisé ne doit pas contenir de lettre à cause de l'unitée de mesure sélectionnée", 422);
            }*/

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id, 'suivi_indicateurable_id' => Auth::user()->profilable->id, 'suivi_indicateurable_type' => get_class(Auth::user()->profilable)]);

            $suiviIndicateur = $this->repository->fill(array_merge($attributs, ["valeurCibleId" => $valeurCibleIndicateur->id]));

            if((auth()->user()->type==="organisation" || get_class(auth()->user()->profilable) == Organisation::class)){
                $suiviIndicateur->estValider = false;
            }

            $suiviIndicateur->save();
            $suiviIndicateur->refresh();

            $valeurRealise = [];
            if ($indicateur->agreger && is_array($attributs["valeurRealise"])) {

                foreach ($attributs["valeurRealise"] as $key => $data) {

                    if (($key = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first())) {
                        $valeur = $suiviIndicateur->valeursRealiser()->create(["value" => $data["value"], "indicateurValueKeyMapId" => $key->pivot->id]);
                        $valeurRealise = array_merge($valeurRealise, ["{$key->key}" => $valeur->value]);
                        //array_push($valeurRealise, ["key" => $key->key, "value" => $valeur->value]);
                    }
                }

            }
            else if (!$indicateur->agreger && !is_array($attributs["valeurRealise"])) {
                $valeur = $suiviIndicateur->valeursRealiser()->create(["value" => $attributs["valeurRealise"], "indicateurValueKeyMapId" => $indicateur->valueKey()->pivot->id]);
                $valeurRealise = array_merge($valeurRealise, ["{$indicateur->valueKey()->key}" => $valeur->value]);

                //$valeurRealise = ["key" => $indicateur->valueKey()->key, "value" => $valeur->value];

            }
            else{
                throw new Exception("Veuillez préciser la valeur cible dans le format adequat.", 400);
            }

            $suiviIndicateur->valeurRealise = $valeurRealise;

            $suiviIndicateur->save();

            if (isset($attributs['commentaire'])) {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $suiviIndicateur->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un suivi indicateur";
                $data['id'] = $suiviIndicateur->id;
                $data['auteurId'] = Auth::user()->id;
                $notification = new CommentaireNotification($data);

                $allUsers = User::where('programmeId', Auth::user()->programmeId);
                foreach ($allUsers as $user) {
                    if ($user->hasPermissionTo('voir-un-commentaire')) {
                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }

            $data['texte'] = "Un suivi d'indicateur vient d'etre faire";
            $data['id'] = $suiviIndicateur->id;
            $data['auteurId'] = Auth::user()->id;
            $notification = new SuiviIndicateurNotification($data);

            $allUsers = User::where('programmeId', Auth::user()->programmeId);
            foreach ($allUsers as $user) {
                if ($user->hasPermissionTo('alerte-suivi-indicateur')) {
                    $user->notify($notification);

                    $notification = $user->notifications->last();

                    event(new NewNotification($this->formatageNotification($notification, $user)));
                }
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisIndicateurResource($suiviIndicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suiviKobo($attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $indicateurs = Indicateur::where('kobo', array_keys($attributs)[1])->where('koboVersion', $attributs['__version__'])->get();

            if (!$indicateurs->count()) throw new Exception("Aucun indicateur trouvé, veillez revoir les paramètres", 400);

            foreach ($indicateurs as $indicateur) {
                if (!($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $indicateur->id)->where("annee", $attributs['annee'])->first())) {

                    if (!array_key_exists('valeurCible', $attributs) || !isset($attributs['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$attributs['annee']} de ce suivi.", 400);

                    $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $indicateur->id, "cibleable_type" => "App\\Models\\Indicateur"]));



                    $valeurCibleIndicateur->save();
                }

                $attributs = array_merge($attributs, [
                    'dateSuivie' => $attributs['_submission_time'],
                    'valeurRealise' => [$attributs[array_keys($attributs)[1]]]
                ]);

                if ($indicateur->unitee_mesure->type && !ctype_digit($attributs[array_keys($attributs)[1]])) {
                    throw new Exception("La valeur realisé ne doit pas contenir de lettre à cause de l'unitée de mesure sélectionnée", 422);
                }

                $suiviIndicateur = $this->repository->fill(array_merge($attributs, ["valeurCibleId" => $valeurCibleIndicateur->id]));

                $suiviIndicateur->save();
            }

            DB::commit();

            //Cache::forget('suiviIndicateurs');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Suivi trimestriel d'un indicateur
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function update($suiviIndicateur, $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (is_string($suiviIndicateur)) {
                $suiviIndicateur = $this->repository->findById($suiviIndicateur);
            } else {
                $suiviIndicateur = $suiviIndicateur;
            }

            unset($attributs['estValider']);

            if(!$suiviIndicateur->programmeId){
                $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id]);
            }

            if (array_key_exists('valeurCible', $attributs) && isset($attributs['valeurCible'])) {
                $suiviIndicateur->valeurCible->valeurCible = $attributs['valeurCible'];

                $suiviIndicateur->valeurCible->save();
            }

            if (array_key_exists('annee', $attributs) && isset($attributs['annee'])) {
                if ($suiviIndicateur->valeurCible->annee != $attributs['annee']) {
                    if (!($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $suiviIndicateur->valeurCible->cibleable_id)->where("annee", $attributs['annee'])->first())) {

                        if (!array_key_exists('valeurCible', $attributs) || !isset($attributs['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$attributs['annee']} de ce suivi.", 400);

                        $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $suiviIndicateur->valeurCible->cibleable_id, "cibleable_type" => "App\\Models\\Indicateur"]));

                        $valeurCibleIndicateur->save();

                        $suiviIndicateur->valeurCibleId = $valeurCibleIndicateur->id;

                        $suiviIndicateur->save();
                    } else {
                        $suiviIndicateur->valeurCible->annee = $attributs['annee'];

                        $suiviIndicateur->valeurCible->save();
                    }
                }
            }

            if (isset($attributs['valeurRealise'])) {
                $suiviIndicateur->valeurRealise = $attributs['valeurRealise'];
            }


            $suiviIndicateur->save();

            $suiviIndicateur = $suiviIndicateur->fresh();

            if (isset($attributs['commentaire'])) {
                $suiviIndicateur->commentaire = $attributs['commentaire'];
                $suiviIndicateur->save();
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $suiviIndicateur->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un suivi indicateur";
                $data['id'] = $suiviIndicateur->id;
                $data['auteurId'] = Auth::user()->id;
                $notification = new CommentaireNotification($data);

                $allUsers = User::where('programmeId', Auth::user()->programmeId)->get();

                foreach ($allUsers as $user) {
                    if ($user->hasPermissionTo('voir-un-commentaire')) {
                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }

            DB::commit();

            //Cache::forget('suiviIndicateurs');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisIndicateurResource($suiviIndicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function valider($suiviIndicateur): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (is_string($suiviIndicateur)) {
                if (!($suiviIndicateur = $this->repository->findById($suiviIndicateur)))  throw new Exception("Suivi indicateur n'existe pas", 500);
            } else {
                $suiviIndicateur = $suiviIndicateur;
            }

            if(!Auth::user()->hasRole('unitee-de-gestion')){
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if($suiviIndicateur->estValider == true){
                return response()->json(['statut' => 'error', 'message' => "Suivi deja valider", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            $suiviIndicateur->estValider = true;

            $suiviIndicateur->save();

            $suiviIndicateur = $suiviIndicateur->fresh();

            DB::commit();

            //Cache::forget('suiviIndicateurs');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisIndicateurResource($suiviIndicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
