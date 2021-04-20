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
    private array $types = [];
    private int $failures = 0;

    public function addSuccessResponse(?string $key, string $body, int $statusCode, array $type): void
    {
        $this->addResponse($key, $body, $statusCode, $type);
    }

    public function addFailureResponse(?string $key, string $body, int $statusCode, array $type): void
    {
        $this->addResponse($key, $body, $statusCode, $type);
        $this->failures++;
    }

    public function responses(): Collection
    {
        return new Collection($this->responses);
    }

    public function decodedResponses(): Collection
    {
        // TODO: Add response decoder
        return $this->responses()->map(fn($response, $key) => $this->decodeResponseType($response, $key));
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

    private function decodeResponseType($response, string $key): mixed
    {
        if ($this->matchResponseType($key, ['application/json', 'text/json'])) {
            return array_merge(
                json_decode($response, true),
                ['status_code' => $this->codes[$key] ?? 0]
            );
        }

        return $response;
    }

    private function matchResponseType(string $key, array $stack): bool
    {
        foreach ($this->types[$key] as $type) {
            return in_array($type, $stack);
        }

        return false;
    }

    private function addResponse(?string $key, string $body, int $statusCode, array $type): void
    {
        $this->responses[$key] = $body;
        $this->codes[$key] = $statusCode;
        $this->types[$key] = $type;
    }
}
