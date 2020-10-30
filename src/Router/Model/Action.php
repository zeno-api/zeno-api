<?php

declare(strict_types=1);

namespace Zeno\Router\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Zeno\Gateway\Model\Service;
use Zeno\Shared\Model\Uuid;

/**
 * @property-read Service $service
 *
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Action extends Model
{
    use Uuid;

    protected $fillable = [
        'response_key',
        'sequence',
        'destination',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
        'sequence' => 'int',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
