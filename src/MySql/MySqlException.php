<?php
declare(strict_types=1);

namespace GreenLinks\EavMySql\MySql;

use Exception;
use Throwable;

class MySqlException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
