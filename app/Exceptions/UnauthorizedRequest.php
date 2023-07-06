<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class UnauthorizedRequest extends \Exception
{
    protected $errorMessage;

    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
        $this->errorMessage = $message;
    }

    public function getResponseMessage() {
        return $this->errorMessage;
    }

    public function toJsonResponse() {
        return new Response(view("error.401"));
    }
}
