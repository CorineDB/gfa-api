<?php

namespace App\Services;

use App\Http\Resources\mods\ModsResource;
use App\Jobs\SendEmailJob;
use App\Repositories\RoleRepository;
use App\Repositories\ModRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ModServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface ModServiceInterface
* @package Core\Services\Interfaces
*/
class ModService extends BaseService implements ModServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $userRepository;

    /**
     * ModService constructor.
     *
     * @param ModRepository $modRepository
     */
    public function __construct(ModRepository $modRepository, UserRepository $userRepository, RoleRepository $roleRepository)
    {
        parent::__construct($modRepository);
        $this->repository = $modRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try
        {

            $mods = [];

            $programme = Auth::user()->programme;

            if( !($programme) ) throw new Exception( "Ce programme n'existe pas", 500);

            $mods = $programme->mods->load('profilable')->pluck("profilable");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ModsResource::collection($mods), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs, $message = null) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $role = $this->roleRepository->findByAttribute('slug', 'mod');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $mod = $this->repository->create([]);

            $mod->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($mod), 'profilable_id' => $mod->id]));

            $mod->user->roles()->attach([$role->id]);

            $mod = $mod->fresh();

            $utilisateur = $mod->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($mod->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé un mod {$mod->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($mod), $mod->id);

            return response()->json(['statut' => 'success', 'message' => "Création du mod réussir", 'data' => new ModsResource($mod), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($modId, array $attributs) : JsonResponse
    {
        try {

            if(is_string($modId))
            {
                $mod = $this->repository->findById($modId);
            }
            else{
                $mod = $modId;
            }

            unset($attributs['email']);

            $mod->user->fill($attributs)->save();

            $mod = $mod->fresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte du mod {$mod->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($mod), $mod->id);

            return response()->json(['statut' => 'success', 'message' => "Compte mod modifié", 'data' => $mod, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
