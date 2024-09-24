<?php
namespace App\Traits\Eloquents;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait HasPermissionTrait
{

    public function givePermissionsTo(...$permissions)
    {

        $permissions = $this->getAllPermissions($permissions);
        dd($permissions);
        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    public function withdrawPermissionsTo(...$permissions)
    {

        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }

    public function refreshPermissions(...$permissions)
    {

        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }

    public function hasPermissionTo($permission)
    {

        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    public function hasPermissionThroughRole($permission)
    {

        foreach ($permission->roles as $role) {
            if ($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(...$roles)
    {

        foreach ($roles as $role) {
            if ($this->roles->contains('slug', $role)) {
                return true;
            }
        }
        return false;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users');
    }
    public function permissions()
    {

        return $this->belongsToMany(Permission::class, 'permission_users');
    }
    protected function hasPermission($permission)
    {

        return (bool) $this->permissions->where('slug', $permission->slug)->count();
    }

    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug', $permissions)->get();
    }

    protected function getUserAllPermissions()
    {
        $permissions = $this->permissions();

        $this->roles->each(function ($role) use ($permissions) {
            $permissions = array_unique(array_merge($permissions, $role->permissions));
        });

        return $permissions;
    }



    /**
     * Attach role and permission to user.
     *
     * @param User $user
     * @param array $roles
     * @param array $permissions
     * @return void
     */
    public function giveAccess(User $user, array $roles, array $permissions)
    {

        if(count($roles) == 0 ){
            $user->roles()->attach(1);
        }
        else{
            $user->roles()->attach($roles);// attach roles to user
        }

        $user->permissions()->attach($permissions);

    }
}
