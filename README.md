### AWS SNS CommunicationService Helper
A helper class to manage AWS SNS topics and subscriptions.

#### Setup
- Setup AWS SDK for Laravel as described [here](#https://github.com/aws/aws-sdk-php-laravel)
- Create SNS client and pass it to constructor
```
$snsClient = App::make('aws')->createClient('sns');
$thread = new ThreadManager($snsClient);

```
### Thread Operations
- `createThread($threadName)`: Returns thread id/arn
- `deleteThread($threadId)`: Deletes thread, returns null
- `getThreadList()`: Retrieve list of all available threads

Example use:
```
$threadList = $thread->getThreadList();
foreach($threadList as $thread){
    echo $thread['TopicArn']
}
```


### Subscription Operations
- `topicId($id)`: Set topic id
- `withEmail($email)`: Set email for subscription
- `withSMS($sms)`: Set sms for subscription
- `withApp($appId)`: Set app for subscription
- `subscribe()`: Subscribe user based on setup. Returns subscription id if platforms does not require confirmation

Example:
```
$result = $thread
    ->threadId('<threadId/ARN>')
    ->withEmail('test@test.com')
    ->subscribe();
```
