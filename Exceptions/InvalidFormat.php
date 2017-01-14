<?php

namespace App\CommunicationService\Exceptions;

use Exception;

class InvalidFormat extends Exception
{
    /**
     * @return static
     */
    public static function genericError(Exception $e)
    {
        return new static("An error occured. Error: {$exception->getMessage()}");
    }

    /**
     * @return static
     */
    public static function missingEndpoints()
    {
        return new static('Protocol and enpoint id/arn have to be defined.');
    }

    /**
     * @return static
     */
    public static function missingThreadId()
    {
        return new static('Thread id/arn has to be defined.');
    }
}
