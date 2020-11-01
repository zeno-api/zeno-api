<?php

declare(strict_types=1);

namespace Zeno\Auth\Security\Guard;

use Illuminate\Http\Request;
use Zeno\Auth\Dto\User;
use Zeno\Auth\Model\Auth;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface Guard
{
    public function name(): string;

    public function user(Auth $auth, Request $request): ?User;
}
