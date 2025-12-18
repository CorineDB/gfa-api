<?php

namespace App\Services;

use App\Http\Resources\user\UniteeGestionResource;
use App\Jobs\SendEmailJob;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UniteeDeGestionRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\UniteeDeGestionServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface UniteeDeGestionServiceInterface
* @package Core\Services\Interfaces
*/
class UniteeDeGestionService extends BaseService implements UniteeDeGestionServiceInterface
{
    use IdTrait, DBStatementTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $programmeRepository, $userRepository;

    /**
     * UniteeDeGestionService constructor.
     *
     * @param UniteeDeGestionRepository $uniteeDeGestionRepository
     */
    public function __construct(UniteeDeGestionRepository $uniteeDeGestionRepository, UserRepository $userRepository, RoleRepository $roleRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($uniteeDeGestionRepository);
        $this->repository = $uniteeDeGestionRepository;
        $this->userRepository = $userRepository;
        $this->programmeRepository = $programmeRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $uniteesDeGestion = UniteeGestionResource::collection($this->repository->all());

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $uniteesDeGestion, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $role = $this->roleRepository->findByAttribute('slug', 'unitee-de-gestion');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $uniteeDeGestion = $this->repository->fill($attributs);

            $uniteeDeGestion->save();

            $uniteeDeGestion->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($uniteeDeGestion), 'profilable_id' => $uniteeDeGestion->id]));

            $uniteeDeGestion->user->roles()->attach([$role->id]);

            $uniteeDeGestion = $uniteeDeGestion->fresh();

            $utilisateur = $uniteeDeGestion->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($uniteeDeGestion->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte admin de l'unitée de gestion {$uniteeDeGestion->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte unitee de gestion créé", 'data' => $uniteeDeGestion, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($uniteeDeGestionId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();
        try {

            if(is_string($uniteeDeGestionId))
            {
                $uniteeDeGestion = $this->repository->findById($uniteeDeGestionId);
            }
            else{
                $uniteeDeGestion = $uniteeDeGestionId;
            }

            unset($attributs['email']);

            $uniteeDeGestion->fill($attributs)->save();

            $uniteeDeGestion->user->fill($attributs)->save();

            $uniteeDeGestion->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de unitée de gestion {$uniteeDeGestion->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($uniteeDeGestion), $uniteeDeGestion->id);

            return response()->json(['statut' => 'success', 'message' => "Compte unitee de gestion modifié", 'data' => $uniteeDeGestion, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
