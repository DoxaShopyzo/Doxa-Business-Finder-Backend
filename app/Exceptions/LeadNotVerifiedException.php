<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class LeadNotVerifiedException extends HttpException
{
    public function __construct(
        string $message = "Lead not yet verified — approve in AI Lead Analyzer before generating a preview",
        protected string $verificationStatus = 'unverified',
        ?\Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(423, $message, $previous, $headers, $code);
    }

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }
}
