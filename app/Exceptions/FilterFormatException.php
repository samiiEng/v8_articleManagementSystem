<?php

namespace App\Exceptions;

use Exception;
use function Psy\debug;

class FilterFormatException extends Exception
{

    public function __construct(string $message = "<div>Your request is incorrectly formatted!</div>", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json("$this->message");
    }

    public function report()
    {
        /*$user = auth()->user()['username'];
        error_log("<div>$user! <div>Your request is incorrectly formatted!</div>");*/
    }
}
