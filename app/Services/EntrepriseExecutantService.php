<?php

namespace App\Services;

use App\Http\Middleware\Authenticate;
use App\Http\Resources\EActiviteResource;
use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\EntrepriseExecutantRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EntrepriseExecutantServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface EntrepriseExecutantServiceInterface
* @package Core\Services\Interfaces
*/
class EntrepriseExecutantService extends BaseService implements EntrepriseExecutantServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $programmeRepository, $userRepository;

    /**
     * EntrepriseExecutantService constructor.
     *
     * @param EntrepriseExecutantRepository $entrepriseExecutantRepository
     */
    public function __construct(EntrepriseExecutantRepository $entrepriseExecutantRepository, UserRepository $userRepository, RoleRepository $roleRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($entrepriseExecutantRepository);
        $this->repository = $entrepriseExecutantRepository;
        $this->userRepository = $userRepository;
        $this->programmeRepository = $programmeRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $programme = Auth::user()->programme;

            //$entreprisesExecutant = UtilisateurResource::collection($this->repository->all());
            $entreprisesExecutant = $programme->getEntreprises();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UtilisateurResource::collection($entreprisesExecutant), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $role = $this->roleRepository->findByAttribute('slug', 'entreprise-executant');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $entrepriseExecutant = $this->repository->create([]);

            $entrepriseExecutant->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($entrepriseExecutant), 'profilable_id' => $entrepriseExecutant->id]));

            $entrepriseExecutant->user->roles()->attach([$role->id]);

            $entrepriseExecutant = $entrepriseExecutant->fresh();

            $modId = Auth::user()->type == "mod" ? Auth::user()->mod->id : $attributs['modId'];

            $entrepriseExecutant->mods()->attach($modId, ["programmeId" => $attributs['programmeId']]);

            $utilisateur = $entrepriseExecutant->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($entrepriseExecutant->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé un compte pour l'entreprise {$entrepriseExecutant->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($entrepriseExecutant), $entrepriseExecutant->id);

            return response()->json(['statut' => 'success', 'message' => "Compte créé", 'data' => $entrepriseExecutant, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($entrepriseExecutantId, array $attributs) : JsonResponse
    {
        try {
            if(is_string($entrepriseExecutantId))
            {
                $entrepriseExecutant = $this->repository->findById($entrepriseExecutantId);
            }
            else{
                $entrepriseExecutant = $entrepriseExecutantId;
            }

            unset($attributs['email']);

            //unset($attributs['programmeId']);

            $entrepriseExecutant->user->fill($attributs)->save();

            $entrepriseExecutant = $entrepriseExecutant->fresh();

            $entrepriseExecutant->mods()->sync([$attributs['modId'] => ["programmeId" => $attributs['programmeId']]]);

            $entrepriseExecutant->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de l'entreprise {$entrepriseExecutant->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($entrepriseExecutant), $entrepriseExecutant->id);

            return response()->json(['statut' => 'success', 'message' => "Compte modifié", 'data' => $entrepriseExecutant, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function eActivites($entrepriseExecutantId): JsonResponse
    {

        try {

            $user = Auth::user();
            $programme = $user->programme;
            $entrepriseExecutant = $this->repository->findById($entrepriseExecutantId);

            if($entrepriseExecutant->user->programmeId != $programme->id) throw new Exception( "Pas le même programme", 500);

            $eActivites = EActiviteResource::collection($entrepriseExecutant->eActivites);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $eActivites, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
