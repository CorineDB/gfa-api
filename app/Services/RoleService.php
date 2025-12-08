<?php

namespace App\Services;

use App\Http\Resources\roles\RolesResource;
use App\Models\Permission;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\RoleServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface RoleServiceInterface
* @package Core\Services\Interfaces
*/
class RoleService extends BaseService implements RoleServiceInterface
{
    /**
     * @var service
     */
    protected $repository;
    protected $permissionRepository;

    /**
     * RoleService constructor.
     *
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        parent::__construct($roleRepository);
        $this->repository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $roles = [];

            $user = Auth::user();

            if( ($user && $user->hasRole("administrateur", "super-admin")) )
            {
                $roles = $this->repository->getInstance()->where('roleable_type', "App\\Models\\Administrateur")->where('roleable_id', $user->profilable_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                //$roles = $this->repository->all();
            }
            else
            {
                $roles = $this->repository->getInstance()->where('roleable_type', $user->profilable_type)->where('roleable_id', $user->profilable_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Retourner le token

            return response()->json(['statut' => 'success', 'message' => null, 'data' => RolesResource::collection($roles), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($role, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($role) && !($role = $this->repository->findById($role))) throw new Exception("Role inconnu.", 404);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new RolesResource($role), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            $controle = 0;

            $idSearch = Permission::where('slug', 'voir-un-projet')->first();

            $user = auth()->user();

            if(($user->type == 'admin') || ($user->type == 'administrateur') || ($user->hasRole('administrateur') || $user->profilable_type == "App\\Models\\Administrateur")){

                $attributs = array_merge($attributs, [
                    'roleable_type' => "App\\Models\\Administrateur",
                    'roleable_id'   => $user->profilable_id,
                ]);
            }
            else{
                $roleableType = $user->profilable ? get_class($user->profilable) : auth()->user()->profilable_type;
                $roleableId   = $user->profilable ? $user->profilable->id : auth()->user()->profilable_id;

                $attributs = array_merge($attributs, [
                    'roleable_type' => $roleableType,
                    'roleable_id'   => $roleableId,
                ]);
            }

            $role = $this->repository->fill(array_merge($attributs, ['programmeId' => auth()->user()->programmeId]));

            $role->save();

            if( array_key_exists('permissions', $attributs) && isset($attributs['permissions']))
            {
                // Attacher les permissions au role, même ceux qui ne sont pas encore crée
                foreach ($attributs['permissions'] as $value) {

                    if(is_int($value))
                    {
                        if($value == $idSearch->id) $controle = 1;

                        $role->permissions()->attach($value);
                    }
                    elseif(isset($value['nom']))
                    {
                        $permission = $this->permissionRepository->findByAttribute("nom", $value['nom']);

                        if(!$permission) $permission =  $this->nouvellePermission($this->permissionRepository->fill($value)->toArray());

                        $role->permissions()->save($permission);
                    }
                    else;

                }

                if(!$controle)
                {
                    $role->permissions()->attach($idSearch->id);
                }
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new RolesResource($role), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($idRole, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($idRole))
            {
                $role = $this->repository->findById($idRole);
            }
            else{
                $role = $idRole;
            }

                $user = auth()->user();

                if(($user->type == 'admin') || ($user->type == 'administrateur') || ($user->hasRole('administrateur') || $user->profilable_type == "App\\Models\\Administrateur")){

                    if(($role->roleable_type != "App\\Models\\Administrateur") || $user->profilable_id != $role->roleable_id){
                        throw new Exception("Action non autorisée : vous ne pouvez pas modifier ce rôle car il appartient à une autre entité.", 403);
                    }
                    elseif(($role->roleable_type == "App\\Models\\Administrateur") && $user->profilable_id != $role->roleable_id){
                        $attributs = array_merge($attributs, [
                            'roleable_type' => "App\\Models\\Administrateur",
                            'roleable_id'   => $user->profilable_id,
                        ]);
                    }
                }
                else{
                    if(($role->roleable_type != get_class($user->profilable)) || $user->profilable_id != $role->roleable_id){
                        //if(($role->roleable_type != "App\\Models\\Administrateur") || $user->profilable_id != $role->roleable_id){
                        throw new Exception("Error Processing Request", 403);
                    }
                    elseif(($role->roleable_type == get_class($user->profilable)) && $user->profilable_id != $role->roleable_id){

                        $roleableType = $user->profilable ? get_class($user->profilable) : auth()->user()->profilable_type;
                        $roleableId   = $user->profilable ? $user->profilable->id : auth()->user()->profilable_id;

                        $attributs = array_merge($attributs, [
                            'roleable_type' => $roleableType,
                            'roleable_id'   => $roleableId,
                        ]);
                    }
                }

            $role = $role->fill($attributs);

            $role->save();

            if( array_key_exists("permissions", $attributs) && isset($attributs['permissions']))
            {
                $updateData = [];

                foreach ($attributs['permissions'] as $value) {

                    if(is_int($value))
                        array_push($updateData, $value);

                    elseif(isset($value['nom']))
                    {

                        $permission = $this->permissionRepository->findByAttribute("nom", $value['nom']);

                        if(!$permission) $permission = $this->nouvellePermission($this->permissionRepository->fill($value)->toArray());

                        array_push($updateData, $permission->id);
                    }
                    else;

                }

                // Mis à jour des permissions de ce role
                $role->permissions()->sync($updateData);

            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Le rôle à bien été mis à jour", 'data' => new RolesResource($role), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    private function nouvellePermission($attributs)
    {
        return $this->permissionRepository->create($attributs);
    }
}
