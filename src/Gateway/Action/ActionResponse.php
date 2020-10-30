<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ActionResponse
{
    private int $statusCode;
    private array $data = [];

    public function __construct(array $data, int $statusCode)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
