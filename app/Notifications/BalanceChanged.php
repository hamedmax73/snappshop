<?php

namespace App\Notifications;

use App\Broadcasting\ShortMessageSendingChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BalanceChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected $type, protected $amount)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [ShortMessageSendingChannel::class];
    }

    /**
     * Get the message of sms gateway notification.
     *
     * @param  mixed  $notifiable
     * @return mixed  $gateway
     */
    public function toSMS($notifiable)
    {

        $message = "کاربری گرامی " . $notifiable->name;
        $message .= "\n";
        $message .= "مبلغ " . $this->amount . " ریال ";

        if ($this->type == 'DEPOSIT') {
            $message .= " به حساب شما واریز گردید";
        } else {
            $message .= " از حساب شما برداشت شد";
        }
        $message .= '\n';
        $message .= 'بانک ملی اسنپ!'; // or config('app.name')

        return $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
