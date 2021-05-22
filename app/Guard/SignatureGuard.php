<?php

declare(strict_types=1);

namespace App\Guard;

use DateTime;
use DateTimeZone;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zeno\Http\Request\RequestStack;
use Zeno\Management\Model\Client;
use Zeno\Signature\Claim;
use Zeno\Signature\Signer;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class SignatureGuard implements Guard
{
    use GuardHelpers;

    private RequestStack $requestStack;
    private Signer $signer;

    public function __construct(RequestStack $requestStack, Signer $signer)
    {
        $this->requestStack = $requestStack;
        $this->signer = $signer;
    }

    public function user(): ?Authenticatable
    {
        if (null !== $user = $this->user) {
            return $user;
        }

        $client = $this->getClient();

        if ('local' === app()->environment()) {
            return $client;
        }

        $requestSignature = $this->getSignature();
        $generatedSignature = $this->signer->sign($client->id, $client->secret, $this->makeClaim());

        if ($requestSignature !== $generatedSignature->getToken()) {
            return null;
        }

        return $client;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    private function getSignature(): string
    {
        if (null === $requestSignature = $this->requestStack->header('X-Signature')) {
            throw new InvalidArgumentException('Missing "X-Signature" header');
        }

        return substr($requestSignature, strpos($requestSignature, '=') + 1);
    }

    private function getClient(): Client
    {
        if (null === $clientId = $this->requestStack->header('X-Client-Id')) {
            throw new InvalidArgumentException('Missing "X-Client-Id" header');
        }

        if (null !== $client = Client::find($clientId)) {
            return $client;
        }

        throw new NotFoundHttpException('Client not found.');
    }

    private function getRequestTimestamp(): DateTime
    {
        if (null === $requestTimestamp = $this->requestStack->header('X-Request-Timestamp')) {
            throw new InvalidArgumentException('Missing "X-Request-Timestamp" header');
        }

        if (false === DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $requestTimestamp)) {
            throw new InvalidArgumentException('Invalid value for "X-Request-Timestamp" header');
        }

        return (new DateTime($requestTimestamp))->setTimezone(new DateTimeZone('UTC'));
    }

    private function makeClaim(): Claim
    {
        return new Claim(
            '/'.$this->requestStack->path(),
            $this->requestStack->getContent(),
            $this->getRequestTimestamp()
        );
    }
}
