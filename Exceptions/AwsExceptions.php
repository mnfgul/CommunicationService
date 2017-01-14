<?php

namespace App\CommunicationService\Exceptions;

use Exception;

class AwsExceptions extends Exception
{
    /**
     * @return static
     */
    public static function defaultError(Exception $e)
    {
        return new static("An error occured while using AWS SNS service. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function invalidParameter(Exception $e)
    {
        return new static("A request parameter does not comply with the associated constraints. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function internalError(Exception $e)
    {
        return new static("An internal service error occured. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function authorizationError(Exception $e)
    {
        return new static("Service has been denied access to the requested AWS SNS resource. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function topicLimitExceeded(Exception $e)
    {
        return new static("This account already owns the maximum allowed number of topics. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function notFoundError(Exception $e)
    {
        return new static("The requested AWS SNS resource does not exist. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }

    /**
     * @return static
     */
    public static function subscriptionLimitExceeded(Exception $e)
    {
        return new static("This account already owns the maximum allowed number of subscriptions. {$e->getAwsErrorCode()}: {$e->getMessage()}");
    }
}
