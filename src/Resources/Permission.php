<?php

namespace Mikaelpopowicz\NovaPermission\Resources;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;
use Mikaelpopowicz\NovaPermission\Models\Permission as PermissionModel;
use Mikaelpopowicz\NovaPermission\Contracts\HasAuthorizable as HasAuthorizableContract;

class Permission extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PermissionModel::class;

    /** @var \Spatie\Permission\PermissionRegistrar */
    protected $registrar;

    /**
     * Permission constructor.
     *
     * @param $resource
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct($resource, PermissionRegistrar $registrar)
    {
        parent::__construct($resource);

        $this->registrar = $registrar;
        $permissionClass = $this->registrar->getPermissionClass();

        static::$model = get_class($permissionClass);

        if ($permissionClass instanceof HasAuthorizableContract) {
            static::$with = ['authorizable'];
        }
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return "Guard: {$this->guard_name}";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'guard_name',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return trans('nova-permission::resources.permission.label');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return trans('nova-permission::resources.permission.singular_label');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $guardOptions = collect(config('auth.guards'))->mapWithKeys(function ($value, $key) {
            return [$key => $key];
        });

        /** @var \Laravel\Nova\Resource $userResource */
        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));
        /** @var \Laravel\Nova\Resource $roleResource */
        $roleResource = Nova::resourceForModel($this->registrar->getRoleClass());

        return [
            ID::make()->sortable(),

            Text::make(ucfirst(trans('validation.attributes.name')), 'name')
                ->rules('required', 'sring', 'max:255')
                ->creationRules('unique:'.config('permission.table_names.permissions'))
                ->updateRules('unique:'.config('permission.table_names.permissions').',name,{{resourceId}}'),

            Select::make(ucfirst(trans('validation.attributes.guard_name')), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),

            DateTime::make(ucfirst(trans('validation.attributes.created_at')), 'created_at')->exceptOnForms(),
            DateTime::make(ucfirst(trans('validation.attributes.updated_at')), 'updated_at')->exceptOnForms(),

            BelongsToMany::make($roleResource::label(), 'roles', $roleResource)
                ->searchable()
                ->singularLabel($roleResource::singularLabel()),

            MorphToMany::make($userResource::label(), 'users', $userResource)
                ->searchable()
                ->singularLabel($userResource::singularLabel()),
        ];
    }
}
