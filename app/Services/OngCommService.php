<?php

namespace App\Services;

use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\SendEmailJob;
use App\Models\OngCom;
use App\Repositories\OngComRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\OngCommServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
* Interface OngCommServiceInterface
* @package Core\Services\Interfaces
*/
class OngCommService extends BaseService implements OngCommServiceInterface
{
    use IdTrait, DBStatementTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $userRepository;

    /**
     * OngCommService $ongCommService
     *
     * @param OngComRepository $ongComRepository
     */
    public function __construct(OngComRepository $ongComRepository, UserRepository $userRepository, RoleRepository $roleRepository)
    {
        parent::__construct($ongComRepository);
        $this->repository = $ongComRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function ongs(): JsonResponse
    {

        try {

            $ongs = $this->repository->getInstance()
            ->whereHas('user', function($utilisateur) {
                $utilisateur->where('type', '=', 'ong');
            })
            ->orderBy('created_at', 'desc')
            ->get();

            //$ongs = UtilisateurResource::collection($this->repository->newInstance()->where()->all());

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UtilisateurResource::collection($ongs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function agences_communication(): JsonResponse
    {

        try {
            $agences_coms = $this->repository->getInstance()
            ->whereHas('user', function($utilisateur) {
                $utilisateur->where('type', '=', 'agence');
            })
            ->orderBy('created_at', 'desc')
            ->get();

            //$ongs = UtilisateurResource::collection($this->repository->newInstance()->where()->all());

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UtilisateurResource::collection($agences_coms), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs, $message = null) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $role = $this->roleRepository->findByAttribute('slug', $attributs['type']);

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            $ong = $this->repository->create([]);

            $this->changeState(0);

            $ong->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($ong), 'profilable_id' => $ong->id]));

            $this->changeState(1);
            $ong->user->roles()->attach([$role->id]);

            $ong = $ong->fresh();

            $utilisateur = $ong->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();


            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($ong->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $message = $attributs["message"] . $ong->user->nom;

            //LogActivity::addToLog("Enrégistrement", $message, get_class($ong), $ong->id);

            return response()->json(['statut' => 'success', 'message' => "Compte crée", 'data' => new UtilisateurResource($ong), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($ongId, array $attributs) : JsonResponse
    {
        try {
            if(is_string($ongId))
            {
                $ong = $this->repository->findById($ongId);
            }
            else{
                $ong = $ongId;
            }

            unset($attributs['email']);

            $utilisateur = $ong->user->fill($attributs);

            $utilisateur->save();

            $ong->fill($attributs)->save();

            $ong->fresh();

            $message = $attributs["message"] . $ong->user->nom;

            //LogActivity::addToLog("Modification", $message, get_class($ong), $ong->id);

            return response()->json(['statut' => 'success', 'message' => "Compte modifié", 'data' => $ong, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
