<?php

namespace App\Services;

use App\Http\Resources\user\UserResource;
use App\Jobs\SendEmailJob;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\MembreMissionDeControleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
* Interface MembreUniteeDeGestionServiceInterface
* @package Core\Services\Interfaces
*/
class MembreMissionDeControleService extends BaseService implements MembreMissionDeControleServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository;

    /**
     * MembreMissionDeControleService constructor.
     *
     * @param UserRepository $membreMissionDeControleRepository
     */
    public function __construct(UserRepository $membreMissionDeControleRepository, RoleRepository $roleRepository)
    {
        parent::__construct($membreMissionDeControleRepository);
        $this->repository = $membreMissionDeControleRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $membresMissionDeControle = UserResource::collection(Auth::user()->missionDeControle->users);

            //$uniteesDeGestion = UtilisateurResource::collection($this->repository->all());

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $membresMissionDeControle, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $role = $this->roleRepository->findById($attributs['roleId']);

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $attributs = array_merge($attributs, ['password' => $password, 'type' => $role->slug]);

            $utilisateur = $this->repository->create($attributs);

            $utilisateur->roles()->attach([$role->id]);

            $missionDeControle = Auth::user()->missionDeControle;

            $utilisateur->missionDeControles()->attach($missionDeControle->id, ['roleId' => $role->id]);

            $utilisateur->programmes()->attach($missionDeControle->programme->id);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($utilisateur, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom : "Inconnu";

            $message = "L'administrateur de la mission de controle \"{Str::ucfirst($acteur)}\" a créé un compte pour un nouveau membre de controle : \"{$utilisateur->nom} {$utilisateur->prenom}\".";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => "Le compte a été créé", 'data' => new UserResource($utilisateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($missionDeControleId, array $attributs) : JsonResponse
    {
        try {

            $role = $this->roleRepository->findById($attributs['roleId']);


            if(is_string($missionDeControleId))
            {
                $missionDeControle = $this->repository->findById($missionDeControleId);
            }
            else{
                $missionDeControle = $missionDeControleId;
            }

            unset($attributs['email']);

            $utilisateur = $missionDeControle->fill($attributs);

            $utilisateur->save();

            $utilisateur->roles()->sync([$role->id]);

            $utilisateur->missionDeControles()->sync(Auth::user()->missionDeControle->id, ['roleId' => $role->id]);

            $acteur = Auth::check() ? Auth::user()->nom : "Inconnu";

            $message = "L'administrateur de la mission de controle \"{Str::ucfirst($acteur)}\" a modifié les informations du membre {$missionDeControle->nom} {$missionDeControle->prenom} de l'unitée.";

            //LogActivity::addToLog("Modification", $message, get_class($missionDeControle), $missionDeControle->id);

            return response()->json(['statut' => 'success', 'message' => "Compte modifié", 'data' => new UserResource($missionDeControle), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
