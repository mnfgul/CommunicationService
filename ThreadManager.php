<?php

namespace App\CommunicationService;

class ThreadManager
{
    public function __construct()
    {
        var_dump('tets');
    }

    public function test()
    {
        var_dump('tets1');
    }

    public function sendnotification(Request $request)
    {
        $applicationArn = 'arn:aws:sns:us-east-1:625640077481:app/MACOS_SANDBOX/MacApp';
        $platform = 'MACOS_SANDBOX';
        $responseMsg = [];

        //create sns client
        $snsClient = App::make('aws')->createClient('sns');

        /*create a topic*/
        $result = $snsClient->createTopic([
            'Name' => 'Event_Topic',
        ]);
        $topicArn = $result['TopicArn'];
        $responseMsg[] = 'Topic is created. Topic_Arn: '.$topicArn;

        //create platform endpoint. this basicly registeres the user with device token. token varies for ios and android or mac
        $token = 'a2bd3e5f81cf7a20d33440585110fa67d2591b5cc057818ceda32bf1191e2d47'; // get this from app
        $result = $snsClient->createPlatformEndpoint([
            'Attributes' => ['Enabled' => 'true'],
            'CustomUserData' => 'My User',
            'PlatformApplicationArn' => $applicationArn,
            'Token' => $token,
        ]);
        $endpointArn = $result['EndpointArn'];
        $responseMsg[] = 'End point created. Endpoint_Arn: '.$endpointArn;

        //Subscribe endpoint(device) to the topic
        $result = $snsClient->subscribe([
            'Endpoint' => $endpointArn,
            'Protocol' => 'application',
            'TopicArn' => $topicArn,
        ]);
        $subscribtionArn = $result['SubscriptionArn'];

        //Confirm the subscription

        //Publish/send message to the endpoint
        $jsonMessage = '{
			"default": "Hi my friend",
			"APNS": "{\"aps\":{\"alert\": \"Hi my friend\"} }",
			"APNS_SANDBOX":"{\"aps\":{\"alert\":\"Hi my friend\"}}",
			"GCM": "{ \"data\": { \"message\": \"Hi my friend\" } }"
			}';

        $result = $snsClient->publish([
            'Message' => $jsonMessage,
            'MessageStructure' => 'json',
            //'PhoneNumber' => null,
            'Subject' => 'New Message',
            //'TargetArn' => '<string>',
            'TopicArn' => $topicArn,
        ]);
        $messageId = $result['MessageId'];

        return response()->json($responseMsg);
    }
}
