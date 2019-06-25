<?php
namespace ManaPHP\Identity;

use ManaPHP\Exception\UnauthorizedException;

class NoCredentialException extends UnauthorizedException
{
    public function __construct($message = 'No Credentials', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
