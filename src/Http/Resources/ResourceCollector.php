<?php
namespace Humi\JsonApiConnector\Http\Resources;

use Humi\JsonApiConnector\Http\Resources\JsonResourceCollection;

class ResourceCollector extends JsonResourceCollection
{
    public $collects = null;

    public function __construct(string $resourceClass, $data)
    {
        $this->collects = $resourceClass;
        parent::__construct($data);
    }
}
