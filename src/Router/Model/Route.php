<?php

declare(strict_types=1);

namespace Zeno\Router\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Zeno\Auth\Model\Auth;
use Zeno\Shared\Model\Uuid;

/**
 * @method static Builder published()
 *
 * @property-read Collection|Action[] $actions
 * @property-read Auth                $auth
 * @property string[]                 $methods
 *
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Route extends Model
{
    use Uuid;

    protected $fillable = [
        'path',
        'methods',
        'available',
        'type',
        'freeze',
        'freeze_ttl',
    ];

    protected $casts = [
        'available'       => 'bool',
        'freeze'          => 'bool',
        'freeze_ttl'      => 'int',
        'methods'         => 'array',
        'forward_headers' => 'array',
    ];

    public function auth(): BelongsTo
    {
        return $this->belongsTo(Auth::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
