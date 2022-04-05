<?php

namespace App\Broadcasting\SendGrid;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendGridMailChannel
{
    /**
     * Send the dynamic template to SendGrid.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        $template = new SendGridTemplateBuilder($notifiable->recipients());

        $body = $notification->toSendGridMail($template);

        if (! $body instanceof SendGridTemplateBuilder) {
            return;
        }

        $options['headers'] = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . config('mail.clients.sendgrid.api_key'),
        ];

        if (! app()->environment('production')) {
            $body->enableSandbox();
        }

        try {
            $client = new Client($options);
            $response = $client->post(config('mail.clients.sendgrid.api_url') . '/mail/send', [
                'body' => json_encode($body->toArray()),
            ]);
        } catch (Exception $e) {
            Log::error('sendgrid api error: ' . $e->getMessage());
        }
    }
}
