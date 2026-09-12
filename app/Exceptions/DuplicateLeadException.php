<?php

namespace App\Exceptions;

use Exception;

class DuplicateLeadException extends Exception
{
    protected $existingLead;

    public function __construct($existingLead, string $message = 'Lead already exists.')
    {
        parent::__construct($message);
        $this->existingLead = $existingLead;
    }

    public function getExistingLead()
    {
        return $this->existingLead;
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'existing_lead' => $this->existingLead,
        ], 409);
    }
}
