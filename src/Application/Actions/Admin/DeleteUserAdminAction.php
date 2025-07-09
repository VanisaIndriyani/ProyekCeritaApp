<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class DeleteUserAdminAction extends Action
{
    private UserRepository $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        if (method_exists($this->userRepository, 'delete')) {
            $this->userRepository->delete($id);
        }
        return $this->respondWithData(['message' => 'User dihapus']);
    }
} 