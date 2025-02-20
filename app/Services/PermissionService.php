<?php

namespace App\Services;

use App\Http\Resources\role\PermissionResource;
use App\Models\Permission;
use App\Models\Role;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface RoleServiceInterface
* @package Core\Services\Interfaces
*/
class PermissionService extends BaseService implements PermissionServiceInterface
{
    /**
     * @var service
     */
    protected $repository;
    protected $roleRepository;

    /**
     * PermissionService constructor.
     *
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository, RoleRepository $roleRepository)
    {
        parent::__construct($permissionRepository);
        $this->repository = $permissionRepository;
        $this->roleRepository = $roleRepository;
    }
    
    
    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $permissions = [];            
            
            $user = Auth::user();

            if( ($user && $user->hasRole("administrateur", "super-admin")) )
            {
                //$permissions = $this->repository->all();
                $permissions = $user->permissions;
            }
            else
            {
                $permissions = $user->permissions;
            }
            
            return response()->json(['statut' => 'success', 'message' => null, 'data' => PermissionResource::collection($permissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $permission = $this->repository->create(['nom' => $attributs['nom'], "description" => $attributs['description'] ]);

            if(isset($attributs['roles']))
            {
                // Attacher les roles lié à la permission, même ceux qui ne sont pas encore crée
                foreach ($attributs['roles'] as $value) {

                    if(is_int($value))
                    {
                        $permission->roles()->attach($value);
                    }
                    elseif(isset($value['nom']))
                    {                    
                        $role = $this->roleRepository->findByAttribute("nom", $value['nom']);

                        if(!$role) $role = $this->nouvelleRole($this->roleRepository->fill($value)->toArray());

                        $permission->roles()->save($role);
                    }
                    else;

                }
            }
            
            DB::commit();
            
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PermissionResource($permission), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($idPermission, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {
            
            parent::update($idPermission, ['nom' => $attributs['nom'], "description" => $attributs['description'] ]);

            $permission = $this->repository->findById($idPermission);

            if(isset($attributs['roles']))
            {
                $updateData = [];

                foreach ($attributs['roles'] as $value) {

                    if(is_int($value))
                        array_push($updateData, $value);

                    elseif(isset($value['nom']))
                    {                    
                        $role = $this->roleRepository->findByAttribute("nom", $value['nom']);

                        if(!$role) $role = $this->nouvelleRole($this->roleRepository->fill($value)->toArray());
                        
                        array_push($updateData, $role->id);
                    }
                    else;

                }
                // Mettre à jour les roles pour ce role
                $permission->roles()->sync($updateData);
                    
            }

            DB::commit();
            
            return response()->json(['statut' => 'success', 'message' => "La permission à bien été mis à jour", 'data' => new PermissionResource($permission), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    private function nouvelleRole($attributs)
    {
        return $this->roleRepository->create($attributs);
    }
}