<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol;

use Illuminate\Support\Collection;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class ProtocolResponses
{
    private array $responses = [];
    private array $codes = [];
    private int $failures = 0;

    public function addSuccessResponse(?string $key, string $body, int $statusCode): void
    {
        $this->addResponse($key, $body, $statusCode);
    }

    public function addFailureResponse(?string $key, string $body, int $statusCode): void
    {
        $this->addResponse($key, $body, $statusCode);
        $this->failures++;
    }

    public function responses(): Collection
    {
        return new Collection($this->responses);
    }

    public function decodedResponses(): Collection
    {
        return $this->responses()->map(fn($response, $key) => array_merge(
            json_decode($response, true),
            ['status_code' => $this->codes[$key] ?? 0]
        ));
    }

    public function codes(): array
    {
        return $this->codes;
    }

    public function totalFailures(): int
    {
        return $this->failures;
    }

    public function hasFailedRequests(): bool
    {
        return $this->failures > 0;
    }

    private function addResponse(?string $key, string $body, int $statusCode): void
    {
        $this->responses[$key] = $body;
        $this->codes[$key] = $statusCode;
    }
}
