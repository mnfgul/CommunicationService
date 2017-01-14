<?php

namespace App\CommunicationService;

use Exception;
use Aws\Sns\SnsClient;
use App\CommunicationService\Exceptions\AwsExceptions;
use App\CommunicationService\Exceptions\InvalidFormat;

class ThreadManager
{
    /** @var Aws\Sns\SnsClient $snsClient*/
    protected $snsClient;

    /** @var string $threadId Current topic id/arn*/
    protected $threadId;

    /** @var string $endPointId End point id/arn/email/sms to be subscribed*/
    protected $endPointId;

    /** @var string $protocol End point protocol to be subscribed*/
    protected $protocol;

    public function __construct(SnsClient $snsClient)
    {
        $this->snsClient = $snsClient;
    }

    /**
     * Set thread name. THIS IS NOT USED FOR NOW.
     *
     * @param string $name
     *
     * @return $this
     */
    public function threadName($name)
    {
        $this->threadName = $name;

        return $this;
    }

    /**
     * Set thread id.
     *
     * @param string $id Thread id/arn
     *
     * @return $this
     */
    public function threadId($id)
    {
        $this->threadId = $id;

        return $this;
    }

    /**
     * Setup to subscribe with email.
     *
     * @param string $email
     * @param bool   $asJson
     *
     * @return $this
     */
    public function withEmail($email, $asJson = false)
    {
        $this->endPointId = $email;
        ($asJson) ? $this->protocol = 'json-email' : $this->protocol = 'email';

        return $this;
    }

    /**
     * Setup to subscribe with SMS.
     *
     * @param string $email
     *
     * @return $this
     */
    public function withSMS($sms)
    {
        $this->endPointId = $sms;
        $this->protocol = 'sms';

        return $this;
    }

    /**
     * Setup to subscribe with mobile or desktop app.
     *
     * @param string $appId
     *
     * @return $this
     */
    public function withApp($appId)
    {
        $this->endPointId = $appId;
        $this->protocol = 'application';

        return $this;
    }

    /**
     * Subscribe the enpoint based on setup.
     *
     * @return string $subscriptionId Returns subscription id/arn for enpoints able to create a subscription immediately
     */
    public function subscribe()
    {
        if (!($this->protocol && $this->endPointId)) {
            throw InvalidFormat::missingEndpoints();
        }

        if (!$this->threadId) {
            throw InvalidFormat::missingThreadId();
        }

        try {
            $result = $this->snsClient->subscribe([
                'Endpoint' => $this->endPointId,
                'Protocol' => $this->protocol, // REQUIRED
                'TopicArn' => $this->threadId, // REQUIRED
            ]);

            return $result['SubscriptionArn'];
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Confirm a subscription type.
     *
     * @param string $token    Temp token sent to user by AWS
     * @param string $threadId Thread id/arn
     *
     * @return string $subscriptionId Returns subscription id/arn
     */
    public function confirmSubscription($token, $threadId)
    {
        try {
            $result = $client->confirmSubscription([
                //'AuthenticateOnUnsubscribe' => false, // use if required
                'Token' => $token, // REQUIRED
                'TopicArn' => $threadId, // REQUIRED
            ]);

            return $result['SubscriptionArn'];
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a thread.
     *
     * @param string $threadName Thread name/title
     *
     * @return string $threadId Created thread id/arn
     */
    public function createThread($threadName)
    {
        //TODO: check for name validation

        try {
            $result = $this->snsClient->createTopic([
                'Name' => $threadName,
            ]);

            return $result['TopicArn'];
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete a thread.
     *
     * @param string $threadId Id of the thread to be deleted
     *
     * @return bool $success
     */
    public function deleteThread($threadId)
    {
        try {
            $this->snsClient->deleteTopic([
                'TopicArn' => $threadId,
            ]);

            return true;
        } catch (Exception $exception) {
            $this->handleException($e);
        }
    }

    /**
     * Gets list of threads.
     *
     * @return array $threadList
     */
    public function getThreadList()
    {
        $threadList = [];
        $firstPage = true;
        $nextToken;

        while ($firstPage || isset($nextToken)) {
            if ($firstPage) {
                $result = $this->snsClient->listTopics();
            } else {
                $result = $this->snsClient->listTopics([
                    'NextToken' => $nextToken,
                ]);

                if ($result->get('NextToken')) {
                    $nextToken = $result->get('NextToken');
                }
            }
            $threadList = array_merge($threadList, $result->get('Topics'));
            $firstPage = false;
        }

        return $threadList;
    }

    /**
     * Handle exceptions.
     *
     * @param Exception $exception
     */
    public function handleException($e)
    {
        if ($e->getAwsErrorCode() !== null) {
            switch ($e->getAwsErrorCode()) {
                case 'InvalidParameter':{
                    throw AwsExceptions::invalidParameter($e);
                }break;
                case 'InternalError':{
                    throw AwsExceptions::internalError($e);
                }break;
                case 'AuthorizationError':{
                    throw AwsExceptions::authorizationError($e);
                }break;
                case 'TopicLimitExceeded':{
                    throw AwsExceptions::topicLimitExceeded($e);
                }break;
                case 'NotFound':{
                    throw AwsExceptions::notFoundError($e);
                }break;
                case 'SubscriptionLimitExceeded':{
                    throw AwsExceptions::subscriptionLimitExceeded($e);
                }break;
                default:{
                    throw AwsExceptions::defaultError($e);
                }
            }
        } else {
            throw InvalidFormat::genericError();
        }
    }
}
