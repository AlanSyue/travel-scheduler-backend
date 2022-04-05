<?php

declare(strict_types=1);

namespace App\Broadcasting\SendGrid;

use Illuminate\Support\Collection;

class SendGridTemplateBuilder
{
    /**
     * The dynamic template ID.
     *
     * @var string
     */
    private $template_id;

    /**
     * The sender name.
     *
     * @var string
     */
    private $sender_name;

    /**
     * The sender email address.
     *
     * @var string
     */
    private $sender_email;

    /**
     * The dynamic template data.
     *
     * @var array
     */
    private $data;

    /**
     * The asm group ID.
     *
     * @var int
     */
    private $asm_group_id;

    /**
     * The asm groups will be displayed.
     *
     * @var array
     */
    private $asm_groups_receiver_display;

    /**
     * Determine if it should use sandbox or not.
     *
     * @var bool
     */
    private $use_sandbox = false;

    /**
     * The categories of the dynamic template.
     *
     * @var array
     */
    private $categories;

    /**
     * The recipients that receive an email notification.
     *
     * @var Collection
     */
    private $recipients;

    /**
     * Create a new builder instance.
     *
     * @param array $recipients
     */
    public function __construct(Collection $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * Get the SendGrid email payload as an array.
     *
     * @param string $email
     * @param string $name
     *
     * @return SendGridTemplateBuilder
     */
    public function sender(string $email, string $name): SendGridTemplateBuilder
    {
        $this->sender_email = $email;
        $this->sender_name = $name;

        return $this;
    }

    /**
     * Get the receiver information
     *
     * @param string $email
     * @param string $name
     *
     * @return SendGridTemplateBuilder
     */
    public function receiver(string $email, string $name): SendGridTemplateBuilder
    {
        $this->receiver_email = $email;
        $this->receiver_name = $name;

        return $this;
    }

    /**
     * Get the dynamic template data.
     *
     * @param array $data
     *
     * @return SendGridTemplateBuilder
     */
    public function templateData(array $data): SendGridTemplateBuilder
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the dynamic template ID.
     *
     * @param string $template_id
     *
     * @return SendGridTemplateBuilder
     */
    public function templateId(string $template_id): SendGridTemplateBuilder
    {
        $this->template_id = $template_id;

        return $this;
    }

    /**
     * handle unsubscribes.
     *
     * @param string $group_id
     * @param array  $groups_receiver_display
     *
     * @return SendGridTemplateBuilder
     */
    public function asm(string $group_id, array $groups_receiver_display = []): SendGridTemplateBuilder
    {
        $this->asm_group_id = $group_id;
        $this->asm_groups_receiver_display = $groups_receiver_display;

        return $this;
    }

    /**
     * Determine if it should use sandbox or not.
     *
     * @return SendGridTemplateBuilder
     */
    public function enableSandbox(): SendGridTemplateBuilder
    {
        $this->use_sandbox = true;

        return $this;
    }

    /**
     * Get the categories of the dynamic template.
     *
     * @param array $categories
     *
     * @return SendGridTemplateBuilder
     */
    public function categories(array $categories): SendGridTemplateBuilder
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Transform the data to array format.
     *
     * @return array
     */
    public function toArray(): array
    {
        $body = [
            'from'  => [
                'name' => $this->sender_name,
                'email' => $this->sender_email,
            ],
            'template_id' => $this->template_id,
            'personalizations' => $this->getPersonalizations(),
            'mail_settings' => [
                'sandbox_mode' => [
                    'enable' => $this->use_sandbox,
                ],
            ],
        ];

        if ($this->asm_group_id) {
            $body['asm'] = [
                'group_id' => $this->asm_group_id,
                'groups_receiver_display' => $this->asm_groups_receiver_display,
            ];
        }

        if ($this->categories) {
            $body['categories'] = $this->categories;
        }

        return $body;
    }

    /**
     * Get the personalization data.
     *
     * @return array
     */
    private function getPersonalizations(): array
    {
        return $this->recipients->map(function ($recipient) {
            return [
                'to' => [$recipient],
                'dynamic_template_data' => $this->data,
            ];
        })->toArray();
    }
}
