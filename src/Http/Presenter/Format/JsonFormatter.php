<?php

declare(strict_types=1);

namespace Zeno\Http\Presenter\Format;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class JsonFormatter implements Formatter
{
    public function format(array $data, int $statusCode): Response
    {
        return new Response(
            json_encode($data),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    public function supports(Request $request): bool
    {
        return $request->acceptsJson();
    }
}
