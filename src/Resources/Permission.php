<?php

namespace Mikaelpopowicz\NovaPermission\Resources;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsToMany;
use Mikaelpopowicz\NovaPermission\Traits\HasFieldName;

class Permission extends Resource
{
    use HasFieldName;

    public static $dateDisplayCallback = null;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model;

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

        $fields = [
            ID::make()->sortable(),
            Text::make($this->getTranslatedFieldName('Name'), 'name')
                ->rules('required', 'string', 'max:255')
                ->creationRules('unique:'.config('permission.table_names.permissions'))
                ->updateRules('unique:'.config('permission.table_names.permissions').',name,{{resourceId}}'),
            Select::make($this->getTranslatedFieldName('Guard name'), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),
        ];

        $authorizableResources = config('nova-permission.authorizable_resources', []);

        if (!empty($authorizableResources)) {
            $fields[] = MorphTo::make($this->getTranslatedFieldName('Authorizable resource'), 'authorizable')
                ->types($authorizableResources)
                ->searchable()
                ->nullable();
        }

        foreach (['Created at', 'Updated at'] as $fieldName) {
            $field = DateTime::make($this->getTranslatedFieldName($fieldName), Str::snake($fieldName))
                ->onlyOnDetail();

            if (is_callable(static::$dateDisplayCallback)) {
                $field->displayUsing(static::$dateDisplayCallback);
            }

            $fields[] = $field;
        }

        $fields[] = BelongsToMany::make(Role::label(), 'roles', Role::class)
            ->searchable()
            ->singularLabel(Role::singularLabel());

        return $fields;
    }
}
