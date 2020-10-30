<?php

declare(strict_types=1);

namespace Zeno\Gateway\Model;

use Illuminate\Database\Eloquent\Model;
use Zeno\Shared\Model\Uuid;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Service extends Model
{
    use Uuid;

    protected $fillable = [
        'name',
        'description',
        'driver',
        'options',
        'available',
    ];

    protected $casts = [
        'available' => 'bool',
        'options'   => 'array',
    ];
}
