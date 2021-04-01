<?php

declare(strict_types=1);

namespace Zeno\Http\Request;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class RequestOptions
{
    const JSON = 'json';
    const QUERY = 'query';
    const MULTIPART = 'multipart';
    const HEADERS = 'headers';
}
