<?php

declare(strict_types=1);

namespace Zeno\Management\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Zeno\Shared\Model\Uuid;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Client extends Model implements AuthenticatableContract
{
    use Uuid;
    use Authenticatable;
}
