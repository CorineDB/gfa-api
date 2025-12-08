<?php

namespace App\Services;

use App\Http\Resources\GouvernementResource;
use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\SendEmailJob;
use App\Repositories\GoouvernementRepository;
use App\Repositories\GouvernementRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\GouvernementServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface GouvernementServiceInterface
* @package Core\Services\Interfaces
*/
class GouvernementService extends BaseService implements GouvernementServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $userRepository;

    /**
     * GouvernementService constructor.
     *
     * @param GouvernementRepository $gouvernementRepository
     */
    public function __construct(GouvernementRepository $gouvernementRepository, UserRepository $userRepository, RoleRepository $roleRepository)
    {
        parent::__construct($gouvernementRepository);
        $this->repository = $gouvernementRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $gouvernements = GouvernementResource::collection($this->repository->all());

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $gouvernements, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $role = $this->roleRepository->findByAttribute('slug', 'gouvernement');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $gouvernement = $this->repository->create([]);

            $gouvernement->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($gouvernement), 'profilable_id' => $gouvernement->id]));

            $gouvernement->user->roles()->attach([$role->id]);

            $gouvernement = $gouvernement->fresh();

            $utilisateur = $gouvernement->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($gouvernement->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte du gouvernement {$gouvernement->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($gouvernement), $gouvernement->id);

            return response()->json(['statut' => 'success', 'message' => "Création du compte gouvernement réussir", 'data' => $gouvernement, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($gouvernementId, array $attributs) : JsonResponse
    {
        try {
            if(is_string($gouvernementId))
            {
                $gouvernement = $this->repository->findById($gouvernementId);
            }
            else{
                $gouvernement = $gouvernementId;
            }

            unset($attributs['email']);

            $gouvernement->fill($attributs)->save();

            $gouvernement->fresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte du gouvernement {$gouvernement->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($gouvernement), $gouvernement->id);

            return response()->json(['statut' => 'success', 'message' => "Compte gouvernement modifié", 'data' => $gouvernement, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
