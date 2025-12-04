<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Traits\Helpers\LogActivity;
use App\Http\Resources\FichierResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\user\UserResource;
use App\Http\Resources\user\TeamMemberResource;
use App\Jobs\SendEmailJob;
use App\Models\Ano;
use App\Models\Password;
use App\Models\Projet;
use App\Models\ReponseAno;
use App\Models\TeamMember;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\IdTrait;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\UserServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
* Interface UserServiceInterface
* @package Core\Services\Interfaces
*/
class UserService extends BaseService implements UserServiceInterface
{
    use IdTrait, HelperTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        parent::__construct($userRepository);
        $this->repository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Récupérer les permissions d'un uitlisateur.
     *
     * @param  $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function permissions($userId): JsonResponse
    {
        try {

            $permissions = [];

            if($utilisateur = $this->repository->findById($userId))
            {
                $permissions = $utilisateur->permissions;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $permissions, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $user = Auth::user();
            $programme = $user->programme;

            if($user->type == 'administrateur' || ($user->hasRole('administrateur') || $user->profilable_type == "App\\Models\\Administrateur")) //$users = User::all();
            {
                /* $users = User::where('programmeId', $programme->id)->
                            where('profilable_id', 0)->
                            where('profilable_type', "App\\Models\\Administrateur")->
                            orWhere('profilable_type', 0)->
                            //where('id', '!=', $user->id)-> */
                            /*w/here('statut', '>', 0)->
                            where('emailVerifiedAt', '!=', null)->*/
                            /* orderBy('nom', 'asc')->
                            get(); */
                $users = User::whereHas("team", function($query){
                    $query->where('profilable_type', "App\\Models\\Administrateur");
                })->where('profilable_id', 0)->where('profilable_type', "App\\Models\\Administrateur")->orderBy('nom', 'asc')->get();
            }

            else
            {
                $users = User::where('programmeId', $programme->id)->
                           where('profilable_type', $user->profilable_type)->
                           where('profilable_id', $user->profilable_id)->
                           where('id', '!=', $user->id)->
                           ///*where('statut', '>', 0)->
                           //where('emailVerifiedAt', '!=', null)->*/
                           orderBy('nom', 'asc')->
                           get();
                $users = User::whereHas("team", function($query) use ($user){
                    $query->where('profilable_type', $user->profilable_type)->
                    where('profilable_id', $user->profilable_id);
                })->where('programmeId', $programme->id)
                    ->where('profilable_type', $user->profilable_type)
                    ->where('profilable_id', $user->profilable_id)
                    ->where('id', '!=', $user->id)
                    ->where('programmeId', $programme->id)
                    ->orderBy('nom', 'asc')->get();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => TeamMemberResource::collection($users), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId]);

            $roles= [];

            foreach ($attributs['roles'] as $role) {

                if( !($role = $this->roleRepository->findById($role)) ) throw new Exception("Role introuvable", 400);

                if(!(auth()->user()->hasRole("administrateur", "super-admin", "organisation", "ong", "agence", "institution", "bailleur", "mission-de-controle", "unitee-de-gestion", "mod", "entreprise-executant" )))  throw new Exception("L'utilisateur a un rôle inconnu", 400);

                array_push($roles, $role->id);
            }

            $password = $this->hashId(8); // Générer le mot de passe

            $attributs = array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'roleId' => $role->id, 'profilable_type' => Auth::user()->profilable_type, 'profilable_id' => Auth::user()->profilable_id]);

            if((auth()->user()->type == 'admin') || (auth()->user()->type == 'administrateur') || (Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $attributs = array_merge($attributs, ['profilable_type' => "App\\Models\\Administrateur"]);
            }

            $utilisateur = $this->repository->fill($attributs);

            $utilisateur->save();

            $utilisateur->roles()->attach($roles);

            $utilisateur->team()->create($attributs);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($utilisateur, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($type), $type->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new TeamMemberResource($utilisateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($utilisateur, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $roles = [];

            foreach ($attributs['roles'] as $role) {

                if( !($role = $this->roleRepository->findById($role)) ) throw new Exception("Role introuvable", 400);

                if(!(auth()->user()->hasRole("administrateur", "super-admin", "ong", "organisation", "agence", "institution", "bailleur", "mission-de-controle", "unitee-de-gestion", "mod" )) ){

                    if(auth()->user()->profilable_type == "App\\Models\\Administrateur" && auth()->user()->profilable_id != $utilisateur->profilable_id){
                        throw new Exception("Vous n'avez pas les permissions de suppresion de cet utilisateur inconnu", 40);
                    }
                    else if(auth()->user()->profilable && (auth()->user()->profilable_type == "App\\Models\\Organisation" || auth()->user()->profilable_type == "App\\Models\\UniteeDeGestion") && auth()->user()->profilable->id != $utilisateur->profilable_id){
                        throw new Exception("Vous n'avez pas les permissions de suppresion de cet utilisateur inconnu", 40);

                    }else{
                        throw new Exception("Vous n'avez pas les permissions de suppresion de cet utilisateur inconnu", 40);
                    }
                }

                array_push($roles, $role->id);
            }

            if(!is_object($utilisateur)) $utilisateur = $this->repository->findById($utilisateur);

            if(!$utilisateur) throw new Exception("Compte utilisateur introuvable", 400);

            if(User::where('contact', $attributs['contact'])->where('id', '!=', $utilisateur->id)->count()) throw new Exception("Ce contact est déja utilisé", 400);

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programmeId]);

            unset($attributs['email']);

            $attributs = array_merge($attributs, ['type' => $role->slug,'roleId' => $role->id]);

            $utilisateur = $utilisateur->fill($attributs);

            $utilisateur->save();

            $utilisateur->roles()->sync($roles);

            if($utilisateur->team){
                $team = $utilisateur->team->fill($attributs);
            }

            $team->save();

            //LogActivity::addToLog("Mis a jour", $message, get_class($utilisateur), $utilisateur->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new TeamMemberResource($utilisateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function createLogo(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $old_logo = $user->logo;

            $this->storeFile($attributs['logo'], 'logo', $user, 80, 'logo');

            if($old_logo != null){

                unlink(public_path("storage/" . $old_logo->chemin));

                if($old_logo){
                    $old_logo->delete();
                }
            }

            //LogActivity::addToLog("Enregistrement", $message, get_class($old_logo), $old_logo->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $user, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPhoto(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $old_photo = $user->photo;

            $this->storeFile($attributs['photo'], 'photo', $user, 80, 'photo');

            if($old_photo != null){

                unlink(public_path("storage/" . $old_photo->chemin));

                if($old_photo){
                    $old_photo->delete();
                }
            }

            //LogActivity::addToLog("Enregistrement", $message, get_class($user), $user->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $user, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getNotifications() : JsonResponse
    {
        try {

            $user = Auth::user();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => NotificationResource::collection($user->unreadNotifications), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function readNotifications(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $notification = $user->notifications->where('id', $attributs['id'])->first();

            if($notification == null) throw new Exception("Notification introuvable", 400);

            $notification->markAsRead();

            $notification->save();

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => 'Notification marquée comme lu', 'data' => NotificationResource::collection($user->unreadNotifications), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteNotifications($id) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $notification = $user->notifications->where('id', $id)->first();

            if($notification == null) throw new Exception("Notification introuvable", 400);

            $notification->delete();

            //LogActivity::addToLog("Suppression", $message, get_class($notification), $notification->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => 'Notification supprimé', 'data' => NotificationResource::collection($user->unreadNotifications), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function deleteAllNotifications() : JsonResponse
    {
        DB::beginTransaction();

        try {

            $user = Auth::user();

            $notifications = $user->unreadNotifications;

            foreach($notifications as $notification)
            {
                $notification->delete();
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => 'Notifications supprimé', 'data' => NotificationResource::collection($user->unreadNotifications), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function fichiers() : JsonResponse
    {
        try {

            $user = Auth::user();

            $fichiers = [];
            $shared = [];

            $fichiers = array_merge($fichiers, ['ano' => FichierResource::collection($user->myFichiers->where('fichiertable_type', get_class(new Ano())))]);

            $fichiers = array_merge($fichiers, ['reponseAno' => FichierResource::collection($user->myFichiers->where('fichiertable_type', get_class(new ReponseAno())))]);

            $fichiers = array_merge($fichiers, ['projet' => FichierResource::collection($user->myFichiers->where('fichiertable_type', get_class(new Projet())))]);

            $fichiers = array_merge($fichiers, ['autre' => FichierResource::collection($user->myFichiers->where('fichiertable_type', 'Autre'))]);

            $shared = array_merge($shared, ['ano' => FichierResource::collection($user->sharedFichiers->where('fichiertable_type', get_class(new Ano())))]);

            $shared = array_merge($shared, ['reponseAno' => FichierResource::collection($user->sharedFichiers->where('fichiertable_type', get_class(new ReponseAno())))]);

            $shared = array_merge($shared, ['projet' => FichierResource::collection($user->sharedFichiers->where('fichiertable_type', get_class(new Projet())))]);

            $shared = array_merge($shared, ['autre' => FichierResource::collection($user->sharedFichiers->where('fichiertable_type', 'Autre'))]);

            $data = [
                'fichiers' => $fichiers,
                'shared' => $shared,
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Réinitialisation du mot de passe de l'utilisateur
     *
     * @param String $token
     * @param array $attributes
     * @return Illuminate\Http\JsonResponse
     */
    public function updatePassword(array $attributes): JsonResponse
    {

        DB::beginTransaction();

        try {

            $utilisateur = Auth::user();

            // S'assurer que le nouveau mot de passe est différent du mot de passe actuel
            if ((Hash::check($attributes['new_password'], $utilisateur->password))) throw new Exception("Le nouveau mot de passe doit être différent de l'actuel mot de passe. Veuillez vérifier", 422);

            if ((Hash::check($attributes['new_password'], $utilisateur->last_password_remember))) throw new Exception("Le mot de passe doit être différent de vos anciens mot de passe. Veuillez changer", 422);

            Password::where("userId", $utilisateur->id)->get()->map(function($item) use ($attributes){

                if( (Hash::check( $attributes['new_password'], $item->password)) )
                {
                    throw new Exception("Le mot de passe doit être différent de vos anciens mot de passe. Veuillez changer", 422);
                }

            });

            Password::create(["password" => $utilisateur->password, "userId" => $utilisateur->id]);

            $utilisateur->last_password_remember = $utilisateur->password;

            // Enrégistrer la donnée
            $utilisateur->password =  Hash::make($attributes['new_password']);

            $utilisateur->password_update_at = now();

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " ". $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient de réinitiliser son mot de passe.";

            //LogActivity::addToLog("Connexion", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Mot de passe réinitialisé', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
