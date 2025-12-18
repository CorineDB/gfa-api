<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\suivis\SuivisResource;
use App\Jobs\GenererPta;
use App\Models\Organisation;
use App\Models\Tache;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\SuiviNotification;
use App\Repositories\SuiviRepository;
use App\Repositories\ComposanteRepository;
use App\Repositories\ActiviteRepository;
use App\Repositories\TacheRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SuiviServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
* Interface SuiviServiceInterface
* @package Core\Services\Interfaces
*/
class SuiviService extends BaseService implements SuiviServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository, $composanteRepository, $activiteRepository, $tacheRepository;

    /**
     * suiviService constructor.
     *
     * @param SuiviRepository $suiviRepository
     */
    public function __construct(SuiviRepository $suiviRepository,
                                ComposanteRepository $composanteRepository,
                                ActiviteRepository $activiteRepository,
                                TacheRepository $tacheRepository)
    {
        parent::__construct($suiviRepository);
        $this->repository = $suiviRepository;
        $this->composanteRepository = $composanteRepository;
        $this->activiteRepository = $activiteRepository;
        $this->tacheRepository = $tacheRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $suivis = [];

            if(Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)){
                $suivis = Auth::user()->profilable->projet->suivis();
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $suivis = Auth::user()->programme->suivis;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($suivis), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des suivis d'un module spécifique
     * @param array $attributs
     * @return Illuminate\Http\JsonResponse
     */
    public function getSuivis($attributs) : JsonResponse
    {

        try
        {
            $suivis = [];

            if($attributs['type'] === "tache")
            {
                $suivis = $this->repository->newInstance()->where("suivitable_type", "App\\Models\\Tache")->orderByDesc("created_at")->get();
            }

            if($attributs['type'] === "activite")
            {
                $suivis = $this->repository->newInstance()->where("suivitable_type", "App\\Models\\Activite")->orderByDesc("created_at")->get();
            }

            if($attributs['type'] === "compopsante")
            {
                $suivis = $this->repository->newInstance()->where("suivitable_type", "App\\Models\\Composante")->orderByDesc("created_at")->get();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisResource::collection($suivis), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            if(!($tache = $this->tacheRepository->findById($attributs['tacheId']))) throw new Exception( "Cette tache n'existe pas", 500);

            $suivi = $tache->suivis()->create(array_merge($attributs, ['poidsActuel'=> $attributs['poidsActuel'], 'programmeId' => auth()->user()->programmeId, "commentaire" => "Etat actuel"]));

            if($tache->activite->statut)

            //$tache->statuts()->create(['etat' => 2]);

            if($attributs["poidsActuel"] == 100){

                $tache->statut = 2;
            }

            if($tache->statut < 0){

                $tache->statut = 0;
            }

            if($tache->activite->statut < 0){

                $tache->activite->statut = 0;
                $tache->activite->save();
            }

            $tache->save();

            $tache =  $tache->fresh();

            /* $suivi =  $suivi->fresh();

            $data['texte'] = "Le suivi de la tache ".$tache->codePta." : ".$tache->nom." vient d'etre fait";
            $data['id'] = $suivi->id;
            $data['auteurId'] = Auth::user()->id;
            $notification = new SuiviNotification($data);

            $allUsers = User::where('programmeId', Auth::user()->programmeId);
            foreach($allUsers as $user)
            {
                if($user->hasPermissionTo('alerte-tache'))
                {
                    $user->notify($notification);

                    $notification = $user->notifications->last();

                    event(new NewNotification($this->formatageNotification($notification, $user)));

                }
            } */

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisResource($suivi), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suiviV2(array $attributs, $tacheId = null) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!($tache = $this->tacheRepository->findById($tacheId ?? $attributs['tacheId']))) throw new Exception( "Cette tache n'existe pas", 500);

            //$suivi = $tache->suivis()->create(array_merge($attributs, ['poidsActuel'=> $tache->poids, 'created_at' => $attributs['date'].' 00:00:00']));

            $suivi = $tache->suivis()->create(array_merge($attributs, ['poidsActuel'=> $attributs['poidsActuel'], 'created_at' => $attributs['date'].' 00:00:00']));

            if($attributs["poidsActuel"]==100){

                $tache->statut = 2;
            }

            //$tache->statuts()->create(['etat' => 2]);

            $suivi->created_at = $attributs['date'].' 00:00:00';

            $suivi->updated_at = $attributs['date'].' 00:00:00';

            $suivi->save();

            $tache->refresh();

            $suivi =  $suivi->fresh();

            $data['texte'] = "Le suivi de la tache ".$tache->codePta." : ".$tache->nom." vient d'etre fait";
            $data['id'] = $suivi->id;
            $data['auteurId'] = Auth::user()->id;
            $notification = new SuiviNotification($data);

            $allUsers = User::where('programmeId', Auth::user()->programmeId);
            foreach($allUsers as $user)
            {
                if($user->hasPermissionTo('alerte-tache'))
                {
                    $user->notify($notification);

                    $notification = $user->notifications->last();

                    event(new NewNotification($this->formatageNotification($notification, $user)));

                }
            }

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisResource($suivi), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
