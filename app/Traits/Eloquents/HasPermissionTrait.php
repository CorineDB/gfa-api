<?php
namespace App\Traits\Eloquents;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait HasPermissionTrait
{

    public function hasPermissionTo($permission)
    {

        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    public function hasPermissionThroughRole($permission)
    {
        if(is_string($permission)){

            return $this->roles->each(function ($role) use ($permission) {
                return ($role->permissions->contains($permission));
            })->count();
            /*foreach ($permission->roles as $role) {
                if ($this->roles->contains($role)) {
                    return true;
                }
            }*/
        }
        else if(is_object($permission)){
            foreach ($permission->roles as $role) {
                if ($this->roles->contains($role)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hasRole(...$roles)
    {
        foreach ($roles as $role) {
            if (is_array($role))
            {
                while (is_array($role)) {
                    foreach ($role as $r) {
                        if ($this->roles->contains('slug', $r) || str_contains($this->type, $r)) {
                            return true;
                        }
                    }
                    $role = null;
                }
            }
            else {
                if ($this->roles->contains('slug', $role) || str_contains($this->type, $role))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Vérifier si un utilisateur à la permission
     *
     * @param $permission
     * @return bool
     */
    protected function hasPermission($permission)
    {
        return (bool) $this->permissions->where('slug', $permission)->count();
    }

    /**
     * Récupérer la liste des roles d'un utilisateur
     *
     * @return List<Role>
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'userId', 'roleId')
                    ->withTimestamps();
    }

    protected function getAllPermissions()
    {
        $permissions = [];

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

    public function canEdit(){


    }
}
