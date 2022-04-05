<?php

namespace Auth\Notifications;

use App\Broadcasting\SendGrid\SendGridMailChannel;
use App\Broadcasting\SendGrid\SendGridTemplateBuilder;
use Auth\Entities\UnverifiedUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmailVerifyNotification extends Notification
{
    use Queueable;

    /**
     * Get the SendGrid dynamic ID.
     */
    private const TEMPLATE_ID = 'd-78793913151a422384f22c350f3f74a5';

    /**
     * Get the notification's delivery channels.
     *
     * @param UnverifiedUser $notifiable
     *
     * @return array
     */
    public function via(UnverifiedUser $notifiable)
    {
        return [SendGridMailChannel::class];
    }

    /**
     * Generate the SendGrid dynamic template.
     *
     * @param SendGridTemplateBuilder $template
     *
     * @return SendGridTemplateBuilder
     */
    public function toSendGridMail(SendGridTemplateBuilder $template): SendGridTemplateBuilder
    {
        return $template->sender(config('mail.from.address'), config('mail.from.name'))
            ->templateId(self::TEMPLATE_ID);
    }
}
