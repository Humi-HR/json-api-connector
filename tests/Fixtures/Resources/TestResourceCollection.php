<?php

namespace Humi\JsonApiConnector\Tests\Fixtures\Resources;

use App\Http\Resources\JsonResourceCollection;

class TestResourceCollection extends JsonResourceCollection
{
    public $collects = TestResource::class;
}
