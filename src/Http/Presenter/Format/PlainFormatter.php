<?php

declare(strict_types=1);

namespace Zeno\Http\Presenter\Format;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class PlainFormatter implements Formatter
{
    public function format($data, int $statusCode): Response
    {
        return new Response($data, $statusCode);
    }

    public function supports(Request $request, $data): bool
    {
        return true;
    }

}
