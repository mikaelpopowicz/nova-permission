<?php

namespace Mikaelpopowicz\NovaPermission\Traits;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasAuthorizations
 *
 * @package Mikaelpopowicz\NovaPermission\Traits
 * @property \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Contracts\Permission $authorizations
 */
trait HasAuthorizations
{
    /**
     * Model's authorizations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function authorizations(): MorphMany
    {
        /** @var \Spatie\Permission\PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);

        return $this->morphMany(get_class($registrar->getPermissionClass()), 'authorizable');
    }
}
