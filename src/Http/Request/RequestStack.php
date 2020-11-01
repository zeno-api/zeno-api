<?php

declare(strict_types=1);

namespace Zeno\Http\Request;

use Illuminate\Http\Request;

/**
 * @mixin Request
 *
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class RequestStack
{
    public function request(): Request
    {
        return request();
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->request(), $method], $parameters);
    }

    public function __get($property)
    {
        return $this->request()->{$property};
    }
}
