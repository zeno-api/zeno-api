<?php

declare(strict_types=1);

namespace Zeno\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use Zeno\Shared\Model\Uuid;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Auth extends Model
{
    use Uuid;

    protected $fillable = [
        'name',
        'driver',
        'options',
    ];

    protected $casts = [
        'options'   => 'array',
        'cache'     => 'bool',
        'cache_ttl' => 'int',
    ];

    public function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}
