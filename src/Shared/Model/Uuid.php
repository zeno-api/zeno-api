<?php

declare(strict_types=1);

namespace Zeno\Shared\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
trait Uuid
{
    public static function bootUuid(): void
    {
        static::creating(function (Model $model) {
            $model->incrementing = false;

            if (null === $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getCasts(): array
    {
        return array_merge(
            $this->casts ?? [],
            ['id' => 'string'],
        );
    }
}
