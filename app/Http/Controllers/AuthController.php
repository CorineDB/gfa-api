<?php

namespace App\Http\Controllers;

use App\Http\Requests\auth\ChangePasswordRequest;
use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Traits\Helpers\IdTrait;
use Core\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $maxAttempts = 3; // Default is 5
    protected $decayMinutes = 1; // Default is 1

    /**
     * @var service
     */
    private $authService;

    /**
     * Instantiate a new AuthController instance.
     * @param AuthServiceInterface $authServiceInterface
     */
    public function __construct(AuthServiceInterface $authServiceInterface)
    {
        $this->middleware(['auth:sanctum'])->only(['deconnexion', 'utilisateurConnecte']);
        $this->authService = $authServiceInterface;

    }

    /**
     * Authentfication et permission d'accès au système
     *
     * @param  App\Http\Requests\auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authentification(LoginRequest $request)
    {
        return $this->authService->authentification($request->all());
    }

    /**
     * Authentfication et permission d'accès au système
     *
     * @param  App\Http\Requests\auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function organisationAuthentification(LoginRequest $request)
    {
        return $this->authService->organisationAuthentification($request->all());
    }

    /**
     * Authentfication et permission d'accès au système
     *
     * @param  App\Http\Requests\auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminAuthentification(LoginRequest $request)
    {
        return $this->authService->adminAuthentification($request->all());
    }

    /**
     * Récupérer l'information de l'utilisateur connecter
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function utilisateurConnecte(Request $request)
    {
        return $this->authService->utilisateurConnecte($request);
    }

    /**
     * Vérification de l'email
     *
     * @param  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationEmailReinitialisationMotDePasse($email)
    {
        return $this->authService->verificationEmailReinitialisationMotDePasse($email);
    }

    /**
     * verification du compte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmationDeCompte($email)
    {
        return $this->authService->confirmationDeCompte($email);
    }

    /**
     * confirmation et activation de compte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activationDeCompte($token)
    {
        return $this->authService->activationDeCompte($token);
    }

    /**
     * verification du compte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationDeCompte($token)
    {
        return $this->authService->verificationDeCompte($token);
    }

    public function debloquer($id)
    {
        return $this->authService->debloquer($id);
    }

    /**
     * Réinitilisation de mot de passe
     *
     * @param  App\Http\Requests\auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reinitialisationDeMotDePasse(ResetPasswordRequest $request)
    {
        return $this->authService->reinitialisationDeMotDePasse($request->all());
    }

    /**
     * Réinitilisation de mot de passe
     *
     * @param  App\Http\Requests\auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modificationDeMotDePasse(ChangePasswordRequest $request)
    {
        return $this->authService->reinitialisationDeMotDePasse($request->all());
    }

    /**
     * Déconnecter l'utilisateur authentifié
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deconnexion(Request $request)
    {
        return $this->authService->deconnexion($request);
    }

    /**
     * Actualiser le token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh_token(Request $request)
    {
        return $this->authService->refresh_token($request);
    }

}
