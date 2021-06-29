<?php


namespace App\Helpers\Exceptions;

use Throwable;

/**
 * model层异常
 */
class ModelException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
