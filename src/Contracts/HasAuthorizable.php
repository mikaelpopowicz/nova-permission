<?php

namespace Mikaelpopowicz\NovaPermission\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface HasAuthorizable
{
    /**
     * Get the authorizable instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function authorizable(): MorphTo;
}
