<?php

namespace App\Tests\Modules\CommonComponents\Fixtures\Resources;

use App\Http\Resources\JsonResource;
use App\Modules\CommonComponents\Interfaces\Resources\ApiResource;

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
