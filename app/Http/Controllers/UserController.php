<?php

namespace App\Http\Controllers;

use App\Http\Requests\user\bailleur\StoreBailleurRequest;
use App\Http\Requests\user\bailleur\UpdateBailleurRequest;
use App\Http\Requests\user\CreateLogoRequest;
use App\Http\Requests\user\StoreUserRequest;
use App\Http\Requests\user\UpdatePasswordRequest;
use App\Http\Requests\user\UpdateUserRequest;
use App\Http\Requests\utilisateur\CreatePhotoRequest;
use App\Http\Requests\utilisateur\ReadNotificationRequest;
use App\Traits\Helpers\IdTrait;
use Core\Services\Interfaces\UserServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use IdTrait;
    /**
     * @var service
     */
    private $userService;

    /**
     * Instantiate a new AuthController instance.
     * @param UserServiceInterface $authServiceInterface
     */
    public function __construct(UserServiceInterface $userServiceInterface)
    {
        $this->middleware('permission:voir-un-utilisateur')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-utilisateur')->only(['update']);
        $this->middleware('permission:creer-un-utilisateur')->only(['store']);
        $this->middleware('permission:supprimer-un-utilisateur')->only(['destroy']);

        $this->userService = $userServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->userService->all();
    }

    /**
     * Display a listing of the permissions of a specific user.
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions( $idUtilisateur )
    {
        return $this->userService->permissions( $idUtilisateur );
    }

    public function bailleurs()
    {
        $bailleur = [];
        $users = User::all();

        foreach($users as $user)
        {
            if($user->hasRole('bailleur'))
            {
                $bailleur = array_merge($bailleur, [$user]);
            }
        }
        return response()->json(['statut' => 'success', 'message' => null, 'data' => $bailleur]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        return $this->userService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idUtilisateur
     * @return \Illuminate\Http\Response
     */
    public function show($idUtilisateur)
    {
        return $this->userService->findById($idUtilisateur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idUtilisateur
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $idUtilisateur)
    {
        return $this->userService->update($idUtilisateur, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $idUtilisateur
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUtilisateur)
    {
        return $this->userService->deleteById($idUtilisateur);
    }

    /**
     * Fonction de création d'un bailleur
     *
     * @param  App\Http\Requests\user\bailleur\StoreBailleurRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function creationDeCompteBailleur(StoreBailleurRequest $request){
        $data = array_merge($request->all(), ['isBailleur' => true]);
        dump($data);
        return $this->userService->create($data);
    }

    /**
     * Fonction de mis à jour des données d'un bailleur
     *
     * @param int $id
     * @param  App\Http\Requests\user\bailleur\UpdateBailleurRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miseAJourDeCompteBailleur(UpdateBailleurRequest $request, $idBailleur){
        $data = array_merge($request->all(), ['isBailleur' => true]);
        return $this->userService->update($idBailleur, $data);
    }

    public function createLogo(CreateLogoRequest $request)
    {
        return $this->userService->createLogo($request->all());
    }

    public function createPhoto(CreatePhotoRequest $request)
    {
        return $this->userService->createPhoto($request->all());
    }

    public function getNotifications()
    {
        return $this->userService->getNotifications();
    }

    public function readNotifications(ReadNotificationRequest $request)
    {
        return $this->userService->readNotifications($request->all());
    }

    public function deleteNotifications($id)
    {
        return $this->userService->deleteNotifications($id);
    }

    public function deleteAllNotifications()
    {
        return $this->userService->deleteAllNotifications();
    }

    public function fichiers()
    {
        return $this->userService->fichiers();
    }

    /**
     * Réinitilisation de mot de passe
     *
     * @param  App\Http\Requests\auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        return $this->userService->updatePassword($request->all());
    }
}
