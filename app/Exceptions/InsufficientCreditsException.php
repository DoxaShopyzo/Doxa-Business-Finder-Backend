<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientCreditsException extends HttpException
{
    public function __construct(string $message = 'Insufficient credits.', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(402, $message, $previous, $headers, $code);
    }
}
