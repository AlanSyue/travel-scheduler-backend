<?php

declare(strict_types=1);

namespace Auth\Services;

use App\Repositories\UserRepositoryInterface;
use Auth\Dtos\AuthDto;
use Auth\Entities\UnverifiedUser;
use Auth\Notifications\EmailVerifyNotification;

class EmailRegisterService
{
    /**
     * The user repository instance.
     *
     * @var UserRepositoryInterface
     */
    private $user_repo;

    /**
     * The email verify notification instance.
     *
     * @var EmailVerifyNotification
     */
    private $notification;

    /**
     * Create a new service instance.
     *
     * @param UserRepositoryInterface $user_repo
     * @param EmailVerifyNotification $notification
     */
    public function __construct(UserRepositoryInterface $user_repo, EmailVerifyNotification $notification)
    {
        $this->user_repo = $user_repo;
        $this->notification = $notification;
    }

    /**
     * Execute the service.
     *
     * @param AuthDto $dto
     *
     * @return void
     */
    public function execute(AuthDto $dto)
    {
        $email = $dto->getEmail();
        $name = $dto->getName();

        $this->user_repo->create($email, $dto->getPassword(), $name);

        $notifiable = new UnverifiedUser($name, $email);

        $notifiable->notify($this->notification);
    }
}
