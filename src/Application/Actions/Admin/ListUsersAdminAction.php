<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class ListUsersAdminAction extends Action
{
    private UserRepository $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->respondWithData($users);
    }
} 