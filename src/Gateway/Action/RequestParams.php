<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

use Borobudur\Component\Parameter\ParameterInterface;
use Zeno\Auth\Dto\User;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class RequestParams
{
    private ?User $user;
    private ParameterInterface $headers;
    private ParameterInterface $params;
    private ParameterInterface $queryParams;
    private ParameterInterface $files;

    public function __construct(?User $user, ParameterInterface $headers, ParameterInterface $params, ParameterInterface $queryParams, ParameterInterface $files)
    {
        $this->user = $user;
        $this->headers = $headers;
        $this->params = $params;
        $this->queryParams = $queryParams;
        $this->files = $files;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function headers(): ParameterInterface
    {
        return $this->headers;
    }

    public function params(): ParameterInterface
    {
        return $this->params;
    }

    public function queryParams(): ParameterInterface
    {
        return $this->queryParams;
    }

    public function files(): ParameterInterface
    {
        return $this->files;
    }
}
