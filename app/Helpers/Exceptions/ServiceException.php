<?php


namespace App\Helpers\Exceptions;

use Throwable;

/**
 * 服务异常
 */
class ServiceException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
