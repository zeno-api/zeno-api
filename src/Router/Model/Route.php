<?php

declare(strict_types=1);

namespace Zeno\Router\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Zeno\Shared\Model\Uuid;

/**
 * @method static Builder published()
 *
 * @property-read Collection|Action[] $actions
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
    ];

    protected $casts = [
        'available' => 'bool',
        'methods'   => 'array',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
