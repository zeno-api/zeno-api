<?php

declare(strict_types=1);

namespace Zeno\Http\Presenter;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Zeno\Http\Presenter\Format\Formatter;
use Zeno\Http\Service\Cors;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class Presenter
{
    /**
     * @var Formatter[]
     */
    private array $formatters = [];
    private Formatter $callbackFormatter;

    public function __construct($formatters, Formatter $callbackFormatter)
    {
        foreach ($formatters as $formatter) {
            $this->addFormatter($formatter);
        }

        $this->callbackFormatter = $callbackFormatter;
    }

    public function addFormatter(Formatter $formatter): void
    {
        $this->formatters[] = $formatter;
    }

    public function format(Request $request, int $statusCode, array $data): Response
    {
        foreach ($this->formatters as $formatter) {
            if (true === $formatter->supports($request)) {
                return $formatter->format($data, $statusCode);
            }
        }

        return $this->callbackFormatter->format($data, $statusCode);
    }

    public function render(Request $request, int $statusCode, array $data): Response
    {
        return $this->format($request, $statusCode, $data)->withHeaders([
            'Via'                          => config('app.response_header_via'),
            $this->getHeaderKey('Version') => config('app.version'),
        ]);
    }

    private function getHeaderKey(string $key): string
    {
        return config('app.response_header_prefix').$key;
    }
}
