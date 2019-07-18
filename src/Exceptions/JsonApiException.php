<?php

namespace Humi\JsonApiConnector\Exceptions;

use Exception;

class JsonApiException extends Exception
{
    public function getErrors(): array
    {
        return json_decode($this->getMessage(), true)['errors'];
    }
}
