<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{
    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserOfId(int $id): User;
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User;
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;
    
    /**
     * Create new user
     */
    public function create(array $userData): int;
}
