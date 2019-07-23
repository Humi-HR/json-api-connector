<?php

namespace Humi\JsonApiConnector\Tests\Fixtures\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TestResourceCollection extends ResourceCollection
{
    public $collects = TestResource::class;
}
