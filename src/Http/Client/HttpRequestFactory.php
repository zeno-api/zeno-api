<?php

declare(strict_types=1);

namespace Zeno\Http\Client;

use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7 as Psr7;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Psr\Http\Message\RequestInterface;
use Zeno\Gateway\Action\RequestParams;
use Zeno\Gateway\Protocol\Driver\Http\HttpRequestFactory as HttpRequestFactoryContract;
use Zeno\Router\Model\Action;
use Zeno\Http\Request\RequestOptions;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
final class HttpRequestFactory implements HttpRequestFactoryContract
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createFromAction(Action $action, RequestParams $requestParams, array $paramsJar): RequestInterface
    {
        $serviceOptions = $this->validate($action->options ?? []);
        $requestOptions = $this->buildOptions($serviceOptions['method'], $requestParams, $paramsJar);

        return $this->applyOptions(
            new Request($serviceOptions['method'], $this->buildUri($action, $paramsJar)),
            $requestOptions
        );
    }

    private function validate(array $options): array
    {
        $validator = $this->validator()->make($options, [
            'method' => 'required|in:get,post,patch,put,delete',
        ]);

        return $validator->validate();
    }

    private function validator(): Factory
    {
        return $this->container->get(Factory::class);
    }

    private function buildUri(Action $action, array $paramsJar): string
    {
        return $this->parseParams(
            sprintf(
                '%s/%s',
                rtrim($action->service->options['base_uri'], '/'),
                ltrim($action->destination, '/')
            ),
            $paramsJar
        );
    }

    private function parseParams(string $url, array $params, string $prefix = ''): string
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $url = $this->parseParams($url, $value, $prefix . $key . '.');
            }

            if (is_scalar($value)) {
                $url = str_replace('{' . $prefix . $key . '}', $value, $url);
            }
        }

        return $url;
    }

    private function buildOptions(string $method, RequestParams $requestParams, array $paramsJar): array
    {
        $headers = array_merge(['Accept' => 'application/json'], $requestParams->headers()->all());
        $files = $requestParams->files();
        $queryParams = [];
        $params = [];

        if (null !== $user = $requestParams->user()) {
            $headers['X-User-Id'] = $user->getIdentifier();
            $headers['X-User'] = json_encode($user->toArray());
        }

        if (in_array(strtolower($method), ['get', 'delete'])) {
            $queryParams = array_merge($requestParams->queryParams()->all(), $paramsJar);
        } else {
            $params = array_merge($requestParams->params()->all(), $paramsJar);
        }

        $options = [RequestOptions::QUERY => $queryParams];

        if ($files->isEmpty() && count($params)) {
            $options[RequestOptions::JSON] = $params;
        } else if (!$files->isEmpty()) {
            $options[RequestOptions::MULTIPART] = array_merge(
                $params,
                $files->all()
            );
        }

        $options[RequestOptions::HEADERS] = $headers;

        return $options;
    }

    private function applyOptions(RequestInterface $request, array &$options): RequestInterface
    {
        $modify = [
            'set_headers' => [],
        ];

        if (isset($options['headers'])) {
            $modify['set_headers'] = $options['headers'];
            unset($options['headers']);
        }

        if (isset($options['form_params'])) {
            if (isset($options['multipart'])) {
                throw new InvalidArgumentException('You cannot use '
                    . 'form_params and multipart at the same time. Use the '
                    . 'form_params option if you want to send application/'
                    . 'x-www-form-urlencoded requests, and the multipart '
                    . 'option to send multipart/form-data requests.');
            }
            $options['body'] = \http_build_query($options['form_params'], '', '&');
            unset($options['form_params']);
            // Ensure that we don't have the header in different case and set the new value.
            $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if (isset($options['multipart'])) {
            $options['body'] = new Psr7\MultipartStream($options['multipart']);
            unset($options['multipart']);
        }

        if (isset($options['json'])) {
            $options['body'] = json_encode($options['json']);
            unset($options['json']);
            // Ensure that we don't have the header in different case and set the new value.
            $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'application/json';
        }

        if (!empty($options['decode_content'])
            && $options['decode_content'] !== true
        ) {
            // Ensure that we don't have the header in different case and set the new value.
            $options['_conditional'] = Psr7\Utils::caselessRemove(['Accept-Encoding'], $options['_conditional']);
            $modify['set_headers']['Accept-Encoding'] = $options['decode_content'];
        }

        if (isset($options['body'])) {
            if (\is_array($options['body'])) {
                throw $this->invalidBody();
            }
            $modify['body'] = Psr7\Utils::streamFor($options['body']);
            unset($options['body']);
        }

        if (!empty($options['auth']) && \is_array($options['auth'])) {
            $value = $options['auth'];
            $type = isset($value[2]) ? \strtolower($value[2]) : 'basic';
            switch ($type) {
                case 'basic':
                    // Ensure that we don't have the header in different case and set the new value.
                    $modify['set_headers'] = Psr7\Utils::caselessRemove(['Authorization'], $modify['set_headers']);
                    $modify['set_headers']['Authorization'] = 'Basic '
                        . \base64_encode("$value[0]:$value[1]");
                    break;
                case 'digest':
                    // @todo: Do not rely on curl
                    $options['curl'][\CURLOPT_HTTPAUTH] = \CURLAUTH_DIGEST;
                    $options['curl'][\CURLOPT_USERPWD] = "$value[0]:$value[1]";
                    break;
                case 'ntlm':
                    $options['curl'][\CURLOPT_HTTPAUTH] = \CURLAUTH_NTLM;
                    $options['curl'][\CURLOPT_USERPWD] = "$value[0]:$value[1]";
                    break;
            }
        }

        if (isset($options['query'])) {
            $value = $options['query'];
            if (\is_array($value)) {
                $value = \http_build_query($value, '', '&', \PHP_QUERY_RFC3986);
            }
            if (!\is_string($value)) {
                throw new InvalidArgumentException('query must be a string or array');
            }
            $modify['query'] = $value;
            unset($options['query']);
        }

        // Ensure that sink is not an invalid value.
        if (isset($options['sink'])) {
            // TODO: Add more sink validation?
            if (\is_bool($options['sink'])) {
                throw new InvalidArgumentException('sink must not be a boolean');
            }
        }

        $request = Psr7\Utils::modifyRequest($request, $modify);
        if ($request->getBody() instanceof Psr7\MultipartStream) {
            // Use a multipart/form-data POST if a Content-Type is not set.
            // Ensure that we don't have the header in different case and set the new value.
            $options['_conditional'] = Psr7\Utils::caselessRemove(['Content-Type'], $options['_conditional']);
            $options['_conditional']['Content-Type'] = 'multipart/form-data; boundary='
                . $request->getBody()->getBoundary();
        }

        // Merge in conditional headers if they are not present.
        if (isset($options['_conditional'])) {
            // Build up the changes so it's in a single clone of the message.
            $modify = [];
            foreach ($options['_conditional'] as $k => $v) {
                if (!$request->hasHeader($k)) {
                    $modify['set_headers'][$k] = $v;
                }
            }
            $request = Psr7\Utils::modifyRequest($request, $modify);
            // Don't pass this internal value along to middleware/handlers.
            unset($options['_conditional']);
        }

        return $request;
    }

    private function invalidBody(): InvalidArgumentException
    {
        return new InvalidArgumentException('Passing in the "body" request '
            . 'option as an array to send a request is not supported. '
            . 'Please use the "form_params" request option to send a '
            . 'application/x-www-form-urlencoded request, or the "multipart" '
            . 'request option to send a multipart/form-data request.');
    }
}
