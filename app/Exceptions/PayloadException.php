<?php

namespace App\Exceptions;

class PayloadException extends \Exception
{
    private array $payload;

    public function __construct(string $message, array $payload, int $code = 0, ?\Throwable $previous = null)
    {
        $this->payload = $payload;
        parent::__construct($message, $code, $previous);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
