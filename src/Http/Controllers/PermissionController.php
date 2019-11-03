<?php

namespace Mikaelpopowicz\NovaPermission\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Nova\Nova;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $roleModel;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $permissionModel;

    public function __construct(PermissionRegistrar $registrar)
    {
        $this->roleModel = $registrar->getRoleClass();
        $this->permissionModel = $registrar->getPermissionClass();
    }

    public function index()
    {
        $roles = $this->roleModel
            ->newQuery()
            ->orderBy('name')
            ->get();

        $rolesArray = $roles->mapWithKeys(function ($role) {
            return [$role->id => false];
        })->toArray();

        $permissions = $this->permissionModel
            ->newQuery()
            ->with('roles', 'authorizable')
            ->get()
            ->map(function ($permission) use ($rolesArray) {
                $permission->all_roles = array_replace(
                    $rolesArray,
                    $permission->roles->mapWithKeys(function ($role) {
                        return [$role->id => true];
                    })->toArray()
                );

                return $permission;
            })
            ->mapToGroups(function ($permission) {
                $data = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard' => $permission->guard_name,
                    'roles' => $permission->all_roles,
                ];

                if ($permission->authorizable) {
                    return [(Nova::newResourceFromModel($permission->authorizable))->title() => $data];
                } else {
                    $payload = explode(' ', $permission->name);
                    $resource = "App\\Nova\\" . Str::studly(array_pop($payload));

                    $key = class_exists($resource)
                        ? $resource::label()
                        : trans('nova-permission::resources.other');

                    return [$key => $data];
                }
            })->toArray();

        return response()->json([
            'groups' => $permissions,
            'roles' => $roles,
        ]);
    }
}
