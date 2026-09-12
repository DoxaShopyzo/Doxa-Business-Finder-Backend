<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DndBlockedException extends HttpException
{
    public function __construct(
        string $message,
        protected string $dndStatus = 'dnd_active',
        ?\Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(403, $message, $previous, $headers, $code);
    }

    public function getDndStatus(): string
    {
        return $this->dndStatus;
    }
}
