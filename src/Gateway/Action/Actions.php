<?php

declare(strict_types=1);

namespace Zeno\Gateway\Action;

use Illuminate\Support\Collection;
use RuntimeException;
use Zeno\Router\Model\Action;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
class Actions extends Collection
{
    public function __construct(array $items = [])
    {
        $this->items = array_map(function ($action) {
            if ($action instanceof Action || $action instanceof Actions) {
                return $action;
            }

            throw new RuntimeException('Unsupported value for Actions');
        }, $items);
    }
}
