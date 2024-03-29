<?php

namespace Humi\JsonApiConnector\Tests\Fixtures\Resources;

use Humi\JsonApiConnector\Http\Resources\JsonResource;
use Humi\JsonApiConnector\Interfaces\ApiResource;

class TestResource extends JsonResource implements ApiResource
{
    protected $type = '';

    public static function getEndpoint(): string
    {
        return 'http://api.io';
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'attributes' => [
            ],
        ];
    }
}
