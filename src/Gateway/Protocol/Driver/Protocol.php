<?php

declare(strict_types=1);

namespace Zeno\Gateway\Protocol\Driver;

use Illuminate\Http\Request;
use Zeno\Gateway\Action\Actions;
use Zeno\Gateway\Protocol\ProtocolResponses;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface Protocol
{
    public function name(): string;

    public function handle(Actions $action, Request $request): ProtocolResponses;
}
