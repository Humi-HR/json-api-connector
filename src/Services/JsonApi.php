<?php

namespace Humi\JsonApiConnector\Services;

use Humi\JsonApiConnector\Interfaces\ApiResource;
use Humi\JsonApiConnector\Exceptions\JsonApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Exception;
use Psr\Http\Message\StreamInterface;

abstract class JsonApi
{
    private $client;

    private $params;

    private $defaultUrl = '';
    private $defaultHeaders = [];

    private $defaultRequiredHeaders = [
        'Content-Type' => 'application/json',
    ];

    private $defaultGuzzleConfig = [
        'http_errors' => false,
    ];

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->params = new stdClass();
    }

    /**
     * Perform an Index request against the api service
     */
    public function index(string $resourceClass, array $queryParams = [])
    {
        $resourceClassInst = new $resourceClass(collect(), [], false);

        $this->throwIfCollectionImplementationError(
            $resourceClassInst,
            'First argument of type ResourceCollection must define collects'
        );

        $collectResourceClass = (new $resourceClass(collect(), [], false))->collects;

        $this->throwIfResourceImplementationError(
            $collectResourceClass,
            'First argument should be a ResourceCollection'
        );

        $endpoint = $this->determineRequestUrl($collectResourceClass, 'index');

        $endpoint .= '?' .
            urldecode(
                http_build_query(
                    $this->mapQueryParams($queryParams)
                )
            );

        $request = new Request(
            SymfonyRequest::METHOD_GET,
            $this->getRequestUrl($endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders())
        );

        return $this->performRequest($request, $resourceClass);
    }

    /**
     * Perform an Show request against the api service
     */
    public function show(string $resourceClass, $id = null, array $queryParams = [])
    {
        $this->throwIfResourceImplementationError($resourceClass);

        $endpoint = $this->determineRequestUrl($resourceClass, 'show');

        if (!empty($id)) {
            $endpoint = $endpoint . '/' . $id;
        }

        $endpoint .= '?' .
            urldecode(
                http_build_query(
                    $this->mapQueryParams($queryParams)
                )
            );

        $request = new Request(
            SymfonyRequest::METHOD_GET,
            $this->getRequestUrl($endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders())
        );

        return $this->performRequest($request, $resourceClass);
    }

    /**
     * Perform a Store request against the api service
     */
    public function store(JsonResource $resource, array $queryParams = [])
    {
        $this->throwIfResourceImplementationError($resource);

        $endpoint = $this->determineRequestUrl($resource, 'store');

        $endpoint .= '?' .
            urldecode(
                http_build_query(
                    $this->mapQueryParams($queryParams)
                )
            );

        $request = new Request(
            SymfonyRequest::METHOD_POST,
            $this->getRequestUrl($endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders()),
            $resource->response()
                ->content()
        );

        return $this->performRequest($request, get_class($resource));
    }

    /**
     * Perform an Update request against the api service
     */
    public function update(JsonResource $resource, string $method = SymfonyRequest::METHOD_PUT, bool $appendId = true, $id = null, $overrideUrl = null)
    {
        $this->throwIfResourceImplementationError($resource);

        $endpoint = $this->determineRequestUrl($resource, 'update');

        if ($appendId && empty($id)) {
            $endpoint = $endpoint . '/' . $resource->id;
        }

        if (!empty($id)) {
            $endpoint = $endpoint . '/' . $id;
        }

        $request = new Request(
            $method,
            $this->getRequestUrl($overrideUrl ?? $endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders()),
            $resource->response()
                ->content()
        );

        return $this->performRequest($request, get_class($resource));
    }

    /**
     * Perform a Destroy request against the api service
     */
    public function destroy(string $resourceClass, $id = null)
    {
        $this->throwIfResourceImplementationError($resourceClass);

        $endpoint = $this->determineRequestUrl($resourceClass, 'destroy');

        if (!empty($id)) {
            $endpoint = $endpoint . '/' . $id;
        }

        $request = new Request(
            SymfonyRequest::METHOD_DELETE,
            $this->getRequestUrl($endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders())
        );

        return $this->performRequest($request, $resourceClass);
    }

    /**
     * Perform an Show request against the api service
     */
    public function raw(string $resourceClass, $id = null, array $queryParams = [])
    {
        $this->throwIfResourceImplementationError($resourceClass);

        $endpoint = $this->determineRequestUrl($resourceClass, 'show');

        if (!empty($id)) {
            $endpoint = $endpoint . '/' . $id;
        }

        $endpoint .= '?' .
            urldecode(
                http_build_query(
                    $this->mapQueryParams($queryParams)
                )
            );

        $request = new Request(
            SymfonyRequest::METHOD_GET,
            $this->getRequestUrl($endpoint),
            array_merge($this->defaultRequiredHeaders, $this->getHeaders())
        );

        return $this->performRequest($request, $resourceClass, $raw = true);
    }

    /**
     * Determine the request url using the API's base url,
     * the resource's endpoint, and the optional override for individual
     * actions
     */
    private function determineRequestUrl($resourceClass, $action = 'index'): string
    {
        $endpoint = $resourceClass::getEndpoint();

        if (method_exists($resourceClass, 'override' . ucfirst($action) . 'Endpoint')) {
            $endpoint = call_user_func(
                [
                    $resourceClass,
                    'override' . ucfirst($action) . 'Endpoint',
                ]
            );
        }

        return $endpoint;
    }

    /**
     * Resources being consumed by the simple api service
     * must implement the ApiResource interface
     */
    private function throwIfResourceImplementationError($resourceClass, $message = null): void
    {
        throw_unless(
            method_exists($resourceClass, 'getEndpoint'),
            InvalidArgumentException::class,
            !is_null($message) ? $message : 'First argument must implement ' . ApiResource::class
        );
    }

    /**
     * Resources being consumed by the simple api service
     * must define the collects property
     */
    private function throwIfCollectionImplementationError($resourceClass, $message = null): void
    {
        throw_unless(
            method_exists($resourceClass, 'collects'),
            InvalidArgumentException::class,
            !is_null($message) ? $message : 'First argument must implement ' . ApiResource::class
        );
    }

    /**
     * Repalce url parameters with params that have been
     * set in the local parameter store
     */
    private function getRequestUrl($endpoint): string
    {
        $url = $this->getUrl() . $endpoint;

        foreach ($this->params as $key => $val) {
            $url = str_replace('{' . $key . '}', $val, $url);
        }

        return $url;
    }

    /**
     * Perform the actual Guzzle request
     * logging the results and continuing if status
     * code is a 200 level, will finally map the response to
     * resources and return
     */
    public function performRequest(Request $request, $resourceClass, $raw = false)
    {
        $response = $this->client->send(
            $request,
            $this->defaultGuzzleConfig
        );

        $this->log($request, $response);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new JsonApiException(
                (string)$response->getBody(),
                $response->getStatusCode()
            );
        }

        if ($raw) {
            return $response->getBody();
        }

        // Empty Json response for parsing 204s
        $json = [
            'data' => [
                'attributes' => [
                ]
            ]
        ];

        if ($response->getStatusCode() !== 204) {
            $json = json_decode($response->getBody(), true);

            throw_if(
                is_null($json),
                Exception::class,
                'API response could not be parsed, likely invalid json in ' . $response->getBody()
            );
        }

        return $this->mapResponseToJsonResource(
            $resourceClass,
            $json
        );
    }

    /**
     * Determine if the returned resource is nested or not,
     * flatted the id and attributes and provide to the constructor
     * of the original resource class
     */
    private function mapResponseToJsonResource($resourceClass, $content): JsonResource
    {
        $data = data_get($content, 'data');

        throw_if(is_null($data), Exception::class, 'Response was not json api format');

        // Entity
        $attributes = data_get($content, 'data.attributes');
        if (is_array($attributes)) {
            $id = data_get($content, 'data.id');

            $attributes['id'] = is_int($id) ? (int) $id : $id;
            $attributes['relationships'] = data_get($content, 'data.relationships');
            $attributes['includeJson'] = data_get($content, 'included');

            return new $resourceClass((object)$attributes);
        }

        // Collection
        $data = collect($data)->map(
            function ($entity) {
                $entity['attributes']['id'] = data_get($entity, 'id');
                $entity['attributes']['relationships'] = data_get($entity, 'relationships');
                $entity['attributes']['includeJson'] = data_get($entity, 'included');

                return $entity['attributes'];
            }
        );

        if (strpos($resourceClass, 'Collection') === false) {
            return $resourceClass::collection($data, data_get($content, 'meta'));
        }

        return new $resourceClass($data, data_get($content, 'meta'));
    }

    /**
     * Magic Method to allow set{PropertyName}() chains
     * for setting internal parameters
     */
    public function __call($funcName, $arguments): self
    {
        throw_if(
            strpos($funcName, 'set') === false,
            Exception::class,
            'Method ' . $funcName . ' does not exist on resource'
        );

        $key = camel_case(str_replace('set', '', $funcName));
        $this->params->$key = $arguments[0];

        return $this;
    }

    protected function mapQueryParams(array $queryParams): array
    {
        return $queryParams;
    }

    /**
     * Get params out of the internal parameter store
     */
    public function getParam(string $key)
    {
        if (!isset($this->params->$key)) {
            throw new Exception('Trying to access undefined api param ' . $key);
        }

        return $this->params->$key;
    }

    /**
     * Url Getter
     */
    public function getUrl(): string
    {
        return $this->defaultUrl;
    }

    /**
     * Headers Getter
     */
    public function getHeaders(): array
    {
        return $this->defaultHeaders;
    }

    /**
     * Log
     */
    public function log(Request $request, Response $response): void
    {
    }
}
