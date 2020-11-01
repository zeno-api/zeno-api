<?php

declare(strict_types=1);

namespace Zeno\Auth\Dto;

use Illuminate\Support\Collection;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class User
{
    private string $identifierName;
    private Collection $payload;

    public function __construct(string $identifierName, array $payload)
    {
        $this->identifierName = $identifierName;
        $this->payload = new Collection($payload);
    }

    public function getIdentifier($default = null)
    {
        return $this->payload->get($this->identifierName, $default);
    }

    public function get(string $key, $default = null)
    {
        return $this->payload->get($key, $default);
    }

    public function toArray(): array
    {
        return $this->payload->toArray();
    }
}
