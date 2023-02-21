<?php

namespace Humi\JsonApiConnector\Exceptions;

use Exception;
use Throwable;

class JsonApiException extends Exception
{
    protected string $errorTitle;
    protected string $internalErrorCode;
    protected array $meta;

    public function __construct(
        $message,
        $code,
        $errorTitle = "",
        $internalCode = "",
        $meta = [],
        Throwable $throwable = null
    )
    {
        parent::__construct($message, $code, $throwable);
        $this->errorTitle = $errorTitle;
        $this->internalErrorCode = $internalCode;
        $this->meta = $meta;
    }

    public function getErrors(): array
    {
        return json_decode($this->getMessage(), true)['errors'];
    }

    public function getErrorTitle()
    {
        return $this->errorTitle;
    }

    public function getInternalErrorCode()
    {
        return $this->internalErrorCode;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
