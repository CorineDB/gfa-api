<?php

namespace App\Services;

use App\Events\Login;
use App\Http\Resources\user\auth\AuthResource;
use App\Http\Resources\user\auth\LoginResource;
use App\Jobs\GenererPta;
use App\Jobs\SendEmailJob;
use App\Models\Organisation;
use App\Models\Password;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\Helpers\ConfigueTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\TooManyFailedAttemptsTrait;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\AuthServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
* Interface AuthServiceInterface
* @package Core\Services\Interfaces
*/
class AuthService extends BaseService implements AuthServiceInterface
{
    use IdTrait, ConfigueTrait, TooManyFailedAttemptsTrait, HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * AuthService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct($userRepository);
        $this->repository = $userRepository;
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param array $identifiants
     * @return Illuminate\Http\JsonResponse
     */
    public function authentification($identifiants): JsonResponse
    {
        $this->checkTooManyFailedAttempts();

        try {

            // Rechercher l'utilisateur grâce à son email.


            // Si la variable utilisateur est null alors une exception sera déclenché notifiant que l'email renseigner ne correspond à aucun enregistrement de la table users
            if ( !($utilisateur = $this->repository->findByAttribute('email', $identifiants['email'])) ){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Identifiant incorrect", 401);

            }

            if($utilisateur->type === 'administrateur' || ($utilisateur->hasRole('administrateur') || $utilisateur->profilable_type == "App\\Models\\Administrateur") || get_class($utilisateur->profilable) !== UniteeDeGestion::class){
                return response()->json(['statut' => 'success', 'message' => 'Action Forbidden', 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            // Vérifier si le mot de passe renseigner correspond au mot de passe du compte uitisateur trouver
            if (!Hash::check($identifiants['password'], $utilisateur->password)){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Mot de passe incorrect", 401);
            }

            // Vérifier si le compte de l'utilisateur est activé ou pas
            if (!$utilisateur->emailVerifiedAt)
            {

                throw new Exception("Veuillez confimer votre compte", 403);

                // Enrégistrement de la date et l'heure de vérification du compte
                // $utilisateur->emailVerifiedAt = now();

                // Sauvegarder les informations
                // $utilisateur->save();
            }

            if ($utilisateur->statut !== 1)
            {
                if ($utilisateur->first_connexion !== 1)
                {
                    throw new Exception("Veuillez réinitialiser votre mot de passe", 403);
                }
                else if ($utilisateur->statut === -1){
                    throw new Exception("Votre compte à été bloquer temporairement. Veuillez contacté votre administrateur. ", 403);
                }
                else{
                    throw new Exception("Votre compte n'est pas activé. Veuillez activer votre compte. ", 403);
                }
            }

            if($utilisateur->lastRequest)
            {
                if((strtotime(date('Y-m-d h:i:s')) - strtotime($utilisateur->lastRequest))/3600 >= 4)
                {
                    $utilisateur->tokens()->delete();
                }
            }

            // Connexion...
            if (!Auth::attempt(['email' => $identifiants["email"], 'password' => $identifiants['password']])){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);

                return response()->json([
                    'status_code' => 401,
                    'message' => 'Unauthorized',
                ]);

                throw new Exception("Erreur de connexion", 500);
            }

            $user = Auth::user();
            //if($user) $userModel = User::find($user->id);

            /*if($user->tokens()->count()){
                throw new Exception("Une session est déjà active pour ce compte. Veuillez vous déconnectez de tous les autres appareils.", 1);
            }*/

            $data = ["access_token" => $user->createToken($this->hashID(8))->plainTextToken, 'expired_at' => now()->addHours(3), 'user' => $user];

            $utilisateur->lastRequest = date('Y-m-d H:i:s');
            $utilisateur->save();

            RateLimiter::clear($this->throttleKey());

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " s'est connecté.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            //GenererPta::dispatch(Auth::user()->programme)->delay(now()->addSeconds(15));

            if((file_exists(storage_path('app')."/pta/pta.json") && Storage::disk('local')->get('pta/pta.json') == "") || !file_exists(storage_path('app')."/pta/pta.json"))
            {
                if(Auth::user()->programme) dispatch(new GenererPta(Auth::user()->programme))->delay(now()->addSeconds(3));
            }

            //event(new Login(Auth::user()->programme));

            // Retourner le token
            return response()->json(['statut' => 'success', 'message' => 'Authentification réussi', 'data' => new LoginResource($data), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)/*->withCookie('XSRF-TOKEN', $data['access_token'], 60*3)*/;

        } catch (\Throwable $th) {


            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param array $identifiants
     * @return Illuminate\Http\JsonResponse
     */
    public function organisationAuthentification($identifiants): JsonResponse
    {
        $this->checkTooManyFailedAttempts();

        try {

            // Rechercher l'utilisateur grâce à son email.


            // Si la variable utilisateur est null alors une exception sera déclenché notifiant que l'email renseigner ne correspond à aucun enregistrement de la table users
            if ( !($utilisateur = $this->repository->findByAttribute('email', $identifiants['email'])) ){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Identifiant incorrect", 401);

            }

            if($utilisateur->type === 'administrateur' || ($utilisateur->hasRole('administrateur') || $utilisateur->profilable_type == "App\\Models\\Administrateur") || get_class($utilisateur->profilable) !== Organisation::class){
                return response()->json(['statut' => 'success', 'message' => 'Action Forbidden', 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            // Vérifier si le mot de passe renseigner correspond au mot de passe du compte uitisateur trouver
            if (!Hash::check($identifiants['password'], $utilisateur->password)){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Mot de passe incorrect", 401);
            }

            // Vérifier si le compte de l'utilisateur est activé ou pas
            if (!$utilisateur->emailVerifiedAt)
            {

                throw new Exception("Veuillez confimer votre compte", 403);

                // Enrégistrement de la date et l'heure de vérification du compte
                // $utilisateur->emailVerifiedAt = now();

                // Sauvegarder les informations
                // $utilisateur->save();
            }

            if ($utilisateur->statut !== 1)
            {
                if ($utilisateur->first_connexion !== 1)
                {
                    throw new Exception("Veuillez réinitialiser votre mot de passe", 403);
                }
                else if ($utilisateur->statut === -1){
                    throw new Exception("Votre compte à été bloquer temporairement. Veuillez contacté votre administrateur. ", 403);
                }
                else{
                    throw new Exception("Votre compte n'est pas activé. Veuillez activer votre compte. ", 403);
                }
            }

            if($utilisateur->lastRequest)
            {
                if((strtotime(date('Y-m-d h:i:s')) - strtotime($utilisateur->lastRequest))/3600 >= 4)
                {
                    $utilisateur->tokens()->delete();
                }
            }

            // Connexion...
            if (!Auth::attempt(['email' => $identifiants["email"], 'password' => $identifiants['password']])){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);

                return response()->json([
                    'status_code' => 401,
                    'message' => 'Unauthorized',
                ]);

                throw new Exception("Erreur de connexion", 500);
            }

            $user = Auth::user();
            //if($user) $userModel = User::find($user->id);

            /*if($user->tokens()->count()){
                throw new Exception("Une session est déjà active pour ce compte. Veuillez vous déconnectez de tous les autres appareils.", 1);
            }*/

            $data = ["access_token" => $user->createToken($this->hashID(8))->plainTextToken, 'expired_at' => now()->addHours(3), 'user' => $user];

            $utilisateur->lastRequest = date('Y-m-d H:i:s');
            $utilisateur->save();

            RateLimiter::clear($this->throttleKey());

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " s'est connecté.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            //GenererPta::dispatch(Auth::user()->programme)->delay(now()->addSeconds(15));

            if((file_exists(storage_path('app')."/pta/pta.json") && Storage::disk('local')->get('pta/pta.json') == "") || !file_exists(storage_path('app')."/pta/pta.json"))
            {
                if(Auth::user()->programme) dispatch(new GenererPta(Auth::user()->programme))->delay(now()->addSeconds(3));
            }

            //event(new Login(Auth::user()->programme));

            // Retourner le token
            return response()->json(['statut' => 'success', 'message' => 'Authentification réussi', 'data' => new LoginResource($data), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)/*->withCookie('XSRF-TOKEN', $data['access_token'], 60*3)*/;

        } catch (\Throwable $th) {


            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param array $identifiants
     * @return Illuminate\Http\JsonResponse
     */
    public function adminAuthentification($identifiants): JsonResponse
    {
        $this->checkTooManyFailedAttempts();

        try {

            // Rechercher l'utilisateur grâce à son email.


            // Si la variable utilisateur est null alors une exception sera déclenché notifiant que l'email renseigner ne correspond à aucun enregistrement de la table users
            if ( !($utilisateur = $this->repository->findByAttribute('email', $identifiants['email'])) ){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Identifiant incorrect", 401);

            }

            if($utilisateur->type !== 'administrateur' && !($utilisateur->hasRole('administrateur') || $utilisateur->profilable_type == "App\\Models\\Administrateur")){
                return response()->json(['statut' => 'success', 'message' => 'Action Forbidden', 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            // Vérifier si le mot de passe renseigner correspond au mot de passe du compte uitisateur trouver
            if (!Hash::check($identifiants['password'], $utilisateur->password)){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);
                throw new Exception("Mot de passe incorrect", 401);
            }

            // Vérifier si le compte de l'utilisateur est activé ou pas
            if (!$utilisateur->emailVerifiedAt)
            {
                throw new Exception("Veuillez confimer votre compte", 403);

                // Enrégistrement de la date et l'heure de vérification du compte
                // $utilisateur->emailVerifiedAt = now();

                // Sauvegarder les informations
                // $utilisateur->save();
            }

            if ($utilisateur->statut !== 1)
            {
                if ($utilisateur->first_connexion !== 1)
                {
                    throw new Exception("Veuillez réinitialiser votre mot de passe", 403);
                }
                else if ($utilisateur->statut === -1){
                    throw new Exception("Votre compte à été bloquer temporairement. Veuillez contacté votre administrateur. ", 403);
                }
                else{
                    throw new Exception("Votre compte n'est pas activé. Veuillez activer votre compte. ", 403);
                }
            }

            if($utilisateur->lastRequest)
            {
                if((strtotime(date('Y-m-d h:i:s')) - strtotime($utilisateur->lastRequest))/3600 >= 4)
                {
                    $utilisateur->tokens()->delete();
                }
            }

            // Connexion...
            if (!Auth::attempt(['email' => $identifiants["email"], 'password' => $identifiants['password']])){

                RateLimiter::hit($this->throttleKey(), $seconds = 60);

                return response()->json([
                    'status_code' => 401,
                    'message' => 'Unauthorized',
                ]);

                throw new Exception("Erreur de connexion", 500);
            }

            $user = Auth::user();
            
            //if($user) $userModel = User::find($user->id);

            /*if($user->tokens()->count()){
                throw new Exception("Une session est déjà active pour ce compte. Veuillez vous déconnectez de tous les autres appareils.", 1);
            }*/

            $data = ["access_token" => $user->createToken($this->hashID(8))->plainTextToken, 'expired_at' => now()->addHours(3), 'user' => $user];

            $utilisateur->lastRequest = date('Y-m-d H:i:s');
            $utilisateur->save();

            RateLimiter::clear($this->throttleKey());

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " s'est connecté.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            //event(new Login(Auth::user()->programme));

            // Retourner le token
            return response()->json(['statut' => 'success', 'message' => 'Authentification réussi', 'data' => new LoginResource($data), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)/*->withCookie('XSRF-TOKEN', $data['access_token'], 60*3)*/;

        } catch (\Throwable $th) {


            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification de compte et permission d'accéder au système grâce au token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request): JsonResponse
    {
        try {
            // retourner les informations de l'utilisateur connecté c'est à dire l'utilisateur qui envoie la requête
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new AuthResource($request->user()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Déconnecter l'utilisateur qui est authentifié et connecter au système.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\JsonResponse
     */
    public function deconnexion(Request $request): JsonResponse
    {
        try {

            $user = Auth::user();

            $acteur = $user ? $user->nom . " ". $user->prenom : "Inconnu";

            // Si la suppression du token ne se passe pas correctement, une exception sera déclenchée
            if( !$request->user()->tokens()->delete() ) throw new Exception("Erreur pendant la déconnexion", 500);

            $message = Str::ucfirst($acteur) . " vient de se déconnecter.";

            //LogActivity::addToLog("Connexion", $message, get_class($user), $user->id);

            return response()->json(['statut' => 'success', 'message' => 'Vous êtes déconnecté', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refresh_token(Request $request)
    {
        try {

            $user = $request->user();

            // Si la suppression du token ne se passe pas correctement, une exception sera déclenchée
            if( !$user->token()->delete() ) throw new Exception("Erreur pendant le processus de rafraichissement du token", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ["access_token" => $user->createToken($this->hashID(8))->plainTextToken], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Activation de compte utilisateur
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if(($utilisateur = $this->repository->findByAttribute('token', $token)) === null) {
                throw new Exception("Veuillez soumettre une demande d'activation de compte", 1);
            }

            if($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande d'activation de votre compte", 1);

            if(!$utilisateur->link_is_valide)  throw new Exception("Lien d'activation de votre compte expiré. Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le lien de vérification de compte n'est plus valide. Veuillez soumettre une nouvelle demande .", 401);

            if($utilisateur->emailVerifiedAt === null){
                // Enrégistrement de la date et l'heure de vérification du compte
                $utilisateur->emailVerifiedAt = now();
            }
            elseif($utilisateur->statut === 0 )
            {
                $utilisateur->statut = 1;
            }
            else{
                throw new Exception("Erreur d'activation du compte", 500);
            }

            //$utilisateur->account_verification_request_sent_at = null;

            $utilisateur->link_is_valide = false;

            //$utilisateur->token = null;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " ". $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient d'activer son compte.";

            //LogActivity::addToLog("Connexion", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte utilisateur activé', 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            if($utilisateur){

                $utilisateur->account_verification_request_sent_at = null;

                $utilisateur->link_is_valide = false;

                $utilisateur->token = null;

                // Sauvegarder les informations
                $utilisateur->save();
            }

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification d'email de réinitialisation de mot de passe
     *
     * @param array $email
     * @return Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email): JsonResponse
    {
        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            $utilisateur = User::where("email", $email)->first();

            // Si l'utilisateur n'existe pas envoyé une reponse avec comme status code 404
            if(!$utilisateur) throw new Exception("Utilisateur inconnu", 500);

            if($utilisateur->statut === 1) throw new Exception("Votre compte est déjà activé", 1);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();

            //Send verificiation email
            dispatch(new SendEmailJob($utilisateur, "confirmation-de-compte"))->delay(now()->addSeconds(15));

            // retourner une reponse avec les détails de l'utilisateur
            return response()->json(['statut' => 'success', 'message' => "E-Mail de d'activation de compte envoyé", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verification de compte utilisateur
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationDeCompte($token): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if(($utilisateur = $this->repository->findByAttribute('token', $token)) === null) {
                throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);
            }

            if($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if(!$utilisateur->link_is_valide) throw new Exception("Lien de réinitialisation de votre mot de passe n'est plus valide. Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le lien de vérification de compte a expiré. Veuillez soumettre une nouvelle demande.", 401);

            $utilisateur->link_is_valide = false;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = $utilisateur ? $utilisateur->nom . " ". $utilisateur->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " vient de confirmer son compte pour la réinitialisation de mot de passe.";

            //LogActivity::addToLog("Confirmation de compte", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte identifier', 'data' => [
               'email' => $utilisateur->email
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function debloquer($id): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if(($utilisateur = $this->repository->findByKey($id)) === null) {
                throw new Exception("Utilisateur introuvalbe", 1);
            }

            $utilisateur->statut = 1;

            // Sauvegarder les informations
            $utilisateur->save();

            DB::commit();

            $acteur = Auth::user()->nom ;

            $message = Str::ucfirst($acteur) . " vient de debloquer " . $utilisateur->nom;

            //LogActivity::addToLog("Deblocage de compte", $message, get_class($utilisateur), $utilisateur->id);

            return response()->json(['statut' => 'success', 'message' => 'Compte debloquer', 'data' => [
               'email' => $utilisateur->email
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérification d'email de réinitialisation de mot de passe
     *
     * @param array $email
     * @return Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email): JsonResponse
    {
        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            $utilisateur = $this->repository->findByAttribute('email', $email);

            // Si l'utilisateur n'existe pas envoyé une reponse avec comme status code 404
            if(!$utilisateur) throw new Exception("Utilisateur inconnu", 500);

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\', '.'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();

            //Send verificiation email
            dispatch(new SendEmailJob($utilisateur, "reinitialisation-mot-de-passe"))->delay(now()->addSeconds(15));

            // retourner une reponse avec les détails de l'utilisateur
            return response()->json(['statut' => 'success', 'message' => "E-Mail de réinitialisation de mot de passe envoyé", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Réinitialisation du mot de passe de l'utilisateur
     *
     * @param String $token
     * @param array $attributes
     * @return Illuminate\Http\JsonResponse
     */
    public function reinitialisationDeMotDePasse(array $attributes): JsonResponse
    {

        DB::beginTransaction();

        try {

            // Rechercher l'utilisateur grâce à l'identifiant.
            if(!($utilisateur = $this->repository->findByAttribute('token', $attributes['token'])))
            {
                throw new Exception("Utilisateur inconnu", 500);
            }
            /*elseif(!($utilisateur = $utilisateur->where('email', $attributes['email'])->first()))
            {
                throw new Exception("Utilisateur inconnu", 500);
            }*/

            if($utilisateur->account_verification_request_sent_at === null) throw new Exception("Veuillez soumettre une demande de réinitilisation de votre mot passe", 1);

            if (Carbon::parse($utilisateur->account_verification_request_sent_at)->addMinutes($this->dureeValiditerLien)->lte(Carbon::now())) throw new Exception("Le delai de validité de votre token est dépassé. Veuillez soumettre une nouvelle demande .", 403);

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

            if($utilisateur->emailVerifiedAt === null){
                // Enrégistrement de la date et l'heure de vérification du compte
                $utilisateur->emailVerifiedAt = now();

                $utilisateur->statut = 1;

                $utilisateur->first_connexion = 1;
            }
            elseif($utilisateur->statut === 0 )
            {
                $utilisateur->statut = 1;

                if($utilisateur->first_connexion === 0) $utilisateur->first_connexion = 1;
            }
            else;

            $utilisateur->account_verification_request_sent_at = null;

            $utilisateur->token = null;

            // Sauvegarder les informations
            $utilisateur->save();

            $utilisateur->tokens()->delete();

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

    /**
     * Authentification à double facteur via SMS
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function authentificationADoubleFacteur(): JsonResponse
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
