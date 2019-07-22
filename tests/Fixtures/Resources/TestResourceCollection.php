<?php

namespace App\Tests\Modules\CommonComponents\Fixtures\Resources;

use App\Http\Resources\JsonResourceCollection;

class TestResourceCollection extends JsonResourceCollection
{
    public $collects = TestResource::class;
}
