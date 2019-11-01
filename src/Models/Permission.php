<?php

namespace Mikaelpopowicz\NovaPermission\Models;

use Spatie\Permission\Models\Permission as Model;
use Mikaelpopowicz\NovaPermission\Traits\HasAuthorizable;
use Mikaelpopowicz\NovaPermission\Contracts\HasAuthorizable as HasAuthorizableContract;

class Permission extends Model implements HasAuthorizableContract
{
    use HasAuthorizable;
}
