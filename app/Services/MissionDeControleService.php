<?php

namespace App\Services;

use App\Http\Resources\mission_de_controles\MissionDeControlesResource;
use App\Jobs\SendEmailJob;
use App\Models\MissionDeControle;
use App\Models\Programme;
use App\Repositories\RoleRepository;
use App\Repositories\MissionDeControleRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\MissionDeControleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface MissionDeControleServiceInterface
* @package Core\Services\Interfaces
*/
class MissionDeControleService extends BaseService implements MissionDeControleServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $programmeRepository, $userRepository;

    /**
     * MissionDeControleService constructor.
     *
     * @param MissionDeControleRepository $missionDeControleRepository
     */
    public function __construct(MissionDeControleRepository $missionDeControleRepository, UserRepository $userRepository, RoleRepository $roleRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($missionDeControleRepository);
        $this->repository = $missionDeControleRepository;
        $this->userRepository = $userRepository;
        $this->programmeRepository = $programmeRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $programme = Programme::find(Auth::user()->programmeId);

            $missionDeControles = [];

            $users = $programme->users->where('profilable_type', get_class(new MissionDeControle()));


            if(Auth::user()->hasRole('bailleur'))
            {
                foreach($users as $user)
                {
                    if($user->missionDeControle->bailleurId == Auth::user()->profilable->id)
                        array_push($missionDeControles, new MissionDeControlesResource($user->missionDeControle));
                }
            }

            else
            {
                foreach($users as $user)
                {
                    array_push($missionDeControles, new MissionDeControlesResource($user->missionDeControle));
                }
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $missionDeControles, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {


            $role = $this->roleRepository->findByAttribute('slug', 'mission-de-controle');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $missionDeControle = $this->repository->create($attributs);

            $missionDeControle->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($missionDeControle), 'profilable_id' => $missionDeControle->id]));

            $missionDeControle->user->roles()->attach([$role->id]);

            $missionDeControle = $missionDeControle->fresh();

            $utilisateur = $missionDeControle->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($missionDeControle->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte admin de la mission de controle {$missionDeControle->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($missionDeControle), $missionDeControle->id);

            return response()->json(['statut' => 'success', 'message' => "Création du compte unitee de gestion réussir", 'data' => $missionDeControle, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($missionDeControleId, array $attributs) : JsonResponse
    {
        try {

            if(is_string($missionDeControleId))
            {
                $missionDeControle = $this->repository->findById($missionDeControleId);
            }
            else{
                $missionDeControle = $missionDeControleId;
            }

            unset($attributs['email']);

            unset($attributs['programmeId']);

            $missionDeControle->user->fill($attributs)->save();

            $missionDeControle->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de la mission de controle {$missionDeControle->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($missionDeControle), $missionDeControle->id);

            return response()->json(['statut' => 'success', 'message' => "Compte de la mission de controle modifié", 'data' => $missionDeControle, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
