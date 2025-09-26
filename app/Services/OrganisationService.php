<?php

namespace App\Services;

use App\Http\Resources\OrganisationResource;
use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\SendEmailJob;
use App\Repositories\RoleRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use App\Models\Projet;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\OrganisationServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\Helpers\Pta;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


/**
* Interface OrganisationServiceInterface
* @package Core\Services\Interfaces
*/
class OrganisationService extends BaseService implements OrganisationServiceInterface
{
    use IdTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $programmeRepository, $userRepository;

    /**
     * OrganisationService constructor.
     *
     * @param OrganisationRepository $organisationRepository
     */
    public function __construct(OrganisationRepository $organisationRepository, UserRepository $userRepository, RoleRepository $roleRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($organisationRepository);
        $this->repository = $organisationRepository;
        $this->userRepository = $userRepository;
        $this->programmeRepository = $programmeRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $organisations = $this->repository->newInstance()::byProgramme()->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationResource::collection($organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($organisationId, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($organisationId) && !($organisationId = $this->repository->findById($organisationId))) throw new Exception("Organisatiob introuvable.", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new OrganisationResource($organisationId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            $role = $this->roleRepository->findByAttribute('slug', 'organisation');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            if((empty($attributs['longitude']) || $attributs['longitude'] == null)){
                $attributs['longitude'] = "2.90000";
            }
            if((empty($attributs['latitude']) || $attributs['latitude'] == null)){
                $attributs['latitude'] = "6.90000";
            }

            $organisation = $this->repository->create($attributs);

            unset($attributs['code']);

            //dd(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($organisation), 'profilable_id' => $organisation->id]));

            $organisation->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($organisation), 'profilable_id' => $organisation->id]));

            $organisation->user->roles()->attach([$role->id]);
            if(isset($attributs['fondId'])){
                $organisation->fonds()->attach($attributs['fondId']);
            }

            $organisation->refresh();

            $utilisateur = $organisation->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();
		\Illuminate\Support\Facades\Log::notice("HERE - ICI");
\Illuminate\Support\Facades\Log::notice("HERE - BEFore END MAIL");

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($organisation->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé un compte pour l'organisation {$organisation->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($organisation), $organisation->id);

            return response()->json(['statut' => 'success', 'message' => "Compte créé", 'data' => new OrganisationResource($organisation), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($organisationId, array $attributs) : JsonResponse
    {
        try {
            if(is_string($organisationId))
            {
                $organisation = $this->repository->findById($organisationId);
            }
            else{
                $organisation = $organisationId;
            }

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id]);

            $organisation->fill($attributs)->save();

            unset($attributs['email']);

            unset($attributs['programmeId']);

            if(isset($attributs['longitude']) && empty($attributs['longitude'])){
                $attributs['longitude'] = 2.90000;
            }
            if(isset($attributs['latitude']) && empty($attributs['latitude'])){
                $attributs['latitude'] = 6.90000;
            }

            unset($attributs['type']);

            $organisation->user->fill($attributs)->save();

            if(isset($attributs['fondId'])){
                $organisation->fonds()->sync($attributs['fondId']);
            }

            $organisation->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte de l'organisation {$organisation->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($organisation), $organisation->id);

            return response()->json(['statut' => 'success', 'message' => "Compte modifié", 'data' => new OrganisationResource($organisation), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
