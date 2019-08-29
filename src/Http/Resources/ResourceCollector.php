<?php
namespace App\Http\Resources;

use JsonResourceCollection;

class ResourceCollector extends JsonResourceCollection
{
    public $collects = null;

    public function __construct(string $resourceClass, $data)
    {
        $this->collects = $resourceClass;
        parent::__construct($data);
    }
}
