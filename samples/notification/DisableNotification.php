<?php
namespace samples\notification;

use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\notification\APIException;
use com\zoho\crm\api\notification\ActionWrapper;
use com\zoho\crm\api\notification\BodyWrapper;
use com\zoho\crm\api\notification\NotificationOperations;
use com\zoho\crm\api\notification\SuccessResponse;
require_once "vendor/autoload.php";

class DisableNotification
{
	public static function initialize()
    {
        $user = new UserSignature('myname@mydomain.com');
        $environment = USDataCenter::PRODUCTION();
        $token = (new OAuthBuilder())
        ->clientId("1000.xxxx")
        ->clientSecret("xxxxxx")
        ->refreshToken("1000.xxxxx.xxxxx")
        ->build();
        (new InitializeBuilder())
            ->user($user)
            ->environment($environment)
            ->token($token)
            ->initialize();
    }

	public static function disableNotification()
	{
		$notificationOperations = new NotificationOperations();
		$bodyWrapper = new BodyWrapper();
		$notificationList = array();
		$notificationClass = 'com\zoho\crm\api\notification\Notification';
		$notification = new $notificationClass();
		$notification->setChannelId("1006800211");
        $events = array();
		array_push($events, "Deals.edit");
		$notification->setEvents($events);
		$notification->setDeleteevents(true);
		array_push($notificationList, $notification);
		$bodyWrapper->setWatch($notificationList);
		$response = $notificationOperations->disableNotification($bodyWrapper);
		if($response != null)
		{
			echo("Status Code: " . $response->getStatusCode() . "\n");
			if($response->isExpected())
			{
				$actionHandler = $response->getObject();
				if($actionHandler instanceof ActionWrapper)
				{
					$actionWrapper = $actionHandler;
					$actionResponses = $actionWrapper->getWatch();
					foreach($actionResponses as $actionResponse)
					{
						if($actionResponse instanceof SuccessResponse)
						{
							$successResponse = $actionResponse;
							echo("Status: " . $successResponse->getStatus()->getValue() . "\n");
							echo("Code: " . $successResponse->getCode()->getValue() . "\n");
							if($successResponse->getDetails() != null)
							{
								echo("Details: \n");
								foreach($successResponse->getDetails() as $keyName => $value)
								{
									if((is_array($value) && sizeof($value) > 0) && isset($value[0]))
									{
										if($value[0] instanceof $notificationClass)
										{
											$eventList = $value;
											foreach($eventList as $event)
											{
												echo("Notification ChannelExpiry: "); print_r($event->getChannelExpiry());
												echo("Notification ResourceUri: " . $event->getResourceUri() . "\n");
												echo("Notification ResourceId: " . $event->getResourceId() . "\n");
												echo("Notification ResourceName: " . $event->getResourceName() . "\n");
												echo("Notification ChannelId: " . $event->getChannelId() . "\n");
											}
										}
									}
									else
									{
										echo($keyName . ": "); print_r($value);
									}
								}
							}
							echo("Message: " . $successResponse->getMessage()->getValue() . "\n");
						}
						else if($actionResponse instanceof APIException)
						{
							$exception = $actionResponse;
							echo("Status: " . $exception->getStatus()->getValue() . "\n");
							echo("Code: " . $exception->getCode()->getValue() . "\n");
							if($exception->getDetails() != null)
							{
								echo("Details: \n");
								foreach ($exception->getDetails() as $keyName => $keyValue)
								{
									echo($keyName . ": " . $keyValue . "\n");
								}
							}
							echo("Message: " . $exception->getMessage()->getValue() . "\n");
						}
					}
				}
				else if($actionHandler instanceof APIException)
				{
					$exception = $actionHandler;
					echo("Status: " . $exception->getStatus()->getValue() . "\n");
					echo("Code: " . $exception->getCode()->getValue() . "\n");
					if($exception->getDetails() != null)
					{
						echo("Details: \n");
						foreach ($exception->getDetails() as $keyName => $keyValue)
						{
							echo($keyName . ": " . $keyValue . "\n");
						}
					}
					echo("Message: " . $exception->getMessage()->getValue() . "\n");
				}
			}
			else
			{
				print_r($response);
			}
		}
	}
}

DisableNotification::initialize();
DisableNotification::disableNotification();