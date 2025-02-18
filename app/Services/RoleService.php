<?php

namespace App\Services;

use App\Http\Resources\roles\RolesResource;
use App\Models\Permission;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\RoleServiceInterface;
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
                $roles = $this->repository->getInstance()->where('roleable_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                //$roles = $this->repository->all();
            }
            else
            {
                $roles = $this->repository->getInstance()->where('roleable_id', $user->id)
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

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $controle = 0;

            $idSearch = Permission::where('slug', 'voir-un-projet')->first();

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
