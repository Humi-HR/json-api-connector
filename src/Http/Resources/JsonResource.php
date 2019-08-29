<?php

namespace Humi\JsonApiConnector\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;
use Illuminate\Http\Resources\Json\JsonResourceCollection as BaseJsonResourceCollection;
use Humi\JsonApiConnector\Http\Resources\JsonResourceCollection;
use Humi\JsonApiConnector\Http\Resources\ResourceCollector;
use Illuminate\Support\Collection;

abstract class JsonResource extends BaseJsonResource
{
    protected $type;

    public function __construct($resource)
    {
        $this->resource = (object) $resource;
    }

    public function __get($key)
    {
        if (!isset($this->resource->{$key})) {
            return null;
        }

        return $this->resource->{$key};
    }

    public static function collect($data): JsonResourceCollection
    {
        return new ResourceCollector(static::class, $data);
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function extractIncluded(BaseJsonResource $resource, string $include): Collection
    {
        return collect($resource->includeJson)
            ->filter(
                function ($included) use ($include) {
                    return $included['type'] === $include;
                }
            );
    }
}
