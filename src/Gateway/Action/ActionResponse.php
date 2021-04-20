<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ActionResponse
{
    private int $statusCode;
    private mixed $data;

    public function __construct($data, int $statusCode)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getData()
    {
        return $this->data;
    }
}
