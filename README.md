# json-api-connector
Allows for simple API communication with a JsonApi compatible endpoint

## Your Service

Extend the base service to define implementation specific values

```php
class PayrollApi extends JsonApi
{
    /**
     * Returns the base url for the API endpoint
     */
    public function getUrl(): string
    {
        return config('app.api.payroll.url');
    }

    /**
     * Required headers, usually authentication
     */
    public function getHeaders(): array
    {
        return [
            'api-key' => config('app.api.payroll.key'),
            'tenant-id' => $this->getParam('tenantId'),
        ];
    }
}
```

## Json Resources

Use the base laravel json api class to represent your external resources.
They should implement Interfaces/ApiResource

```php
class Company extends PayrollResource implements ApiResource
{
    protected $type = 'companies';

    public static function getEndpoint(): string
    {
        return '/companies';
    }

    public function toArray($request)
    {
        return [
            'id' => null,
            'type' => $this->type,
            'attributes' => []
        ]
    }
```

## Calling the resource

Performing a get request

```php
        return app(PayrollService::class)
            ->show(
                Benefit::class, // resource class
                $entityId, // id for use in URI
                $request->query() // query parameters
            );
```
