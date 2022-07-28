<?php

namespace App\Broadcasting;

use App\Interfaces\ShortMessageSenderGatewayInterface;
use Illuminate\Notifications\Notification;
use App\Models\User\User;

class ShortMessageSendingChannel
{

    public function __construct(protected ShortMessageSenderGatewayInterface $gateway)
    {
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSMS($notifiable);

        return $this->gateway->send($notifiable->phone, $message);
    }
}
