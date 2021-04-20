<?php

declare(strict_types=1);

namespace Zeno\Http\Presenter\Format;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface Formatter
{
    public function format($data, int $statusCode): Response;

    public function supports(Request $request, $data): bool;
}
