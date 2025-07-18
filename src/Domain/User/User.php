<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    private ?int $id;

    private string $username;

    private string $firstName;

    private string $lastName;
    
    private string $email;
    
    private string $password;

    private string $role;

    public function __construct(?int $id, string $username, string $firstName, string $lastName, string $role = 'user', string $email = '', string $password = '')
    {
        $this->id = $id;
        $this->username = strtolower($username);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
        $this->role = $role;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getRole(): string
    {
        return $this->role;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function getNama(): string
    {
        // Jika firstName adalah nama lengkap, gunakan itu
        return $this->firstName ?: trim($this->firstName . ' ' . $this->lastName);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
