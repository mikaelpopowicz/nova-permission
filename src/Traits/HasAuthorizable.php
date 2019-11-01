<?php

namespace Mikaelpopowicz\NovaPermission\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trait HasAuthorizable
 *
 * @package Mikaelpopowicz\NovaPermission\Traits
 * @property \Illuminate\Database\Eloquent\Model|null $authorizable
 */
trait HasAuthorizable
{
    /**
     * Get the authorizable instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function authorizable(): MorphTo
    {
        return $this->morphTo();
    }
}
