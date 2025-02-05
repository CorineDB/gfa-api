<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Repositories\RoleRepository;
use App\Repositories\InstitutionRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\InstitutionServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface InstitutionServiceInterface
* @package Core\Services\Interfaces
*/
class InstitutionService extends BaseService implements InstitutionServiceInterface
{
    use IdTrait, DBStatementTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository;

    /**
     * InstitutionService constructor.
     *
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(InstitutionRepository $institutionRepository, RoleRepository $roleRepository)
    {
        parent::__construct($institutionRepository);
        $this->repository = $institutionRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try {

            $institutions = $this->repository->newInstance()->where("type","institution")->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $institutions, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $role = $this->roleRepository->findByAttribute('slug', 'institution');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            //$institution = $this->repository->create([]);

            $this->changeState(0);

            $institution = $this->repository->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug]));

            $this->changeState(1);

            $institution->roles()->attach([$role->id]);

            $institution->account_verification_request_sent_at = Carbon::now();

            $institution->token = str_replace(['/', '\\'], '', Hash::make( $institution->secure_id . Hash::make($institution->email) . Hash::make(Hash::make(strtotime($institution->account_verification_request_sent_at)))));

            $institution->link_is_valide = true;

            $institution->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($institution, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé un compte {$institution->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($institution), $institution->id);

            return response()->json(['statut' => 'success', 'message' => "Création du compte réussir", 'data' => $institution, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($institutionId, array $attributs) : JsonResponse
    {
        try {


            if(is_string($institutionId))
            {
                $institution = $this->repository->findById($institutionId);
            }
            else{
                $institution = $institutionId;
            }

            unset($attributs['email']);

            $utilisateur = $institution->fill($attributs);

            $utilisateur->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte {$institution->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($institution), $institution->id);

            return response()->json(['statut' => 'success', 'message' => "Compte modifié", 'data' => $institution, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
