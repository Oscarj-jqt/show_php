<?php

namespace App\Repository\User;

use App\Model\User;
use App\Repository\User\UserRepositoryInterface;

class UserJsonRepository implements UserRepositoryInterface
{
    private string $dataFile;

    public function __construct(string $dataFile)
    {
        $this->dataFile = $dataFile;
    }

    public function findAll(): array
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $users = [];
        foreach ($data as $item) {
            $users[] = new User(
                $item['id'],
                $item['username'],
                $item['name'],
                $item['firstname'],
                $item['lastname'],
                $item['email'],
                $item['password'],
                $item['role']
            );
        }
        return $users;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->findAll() as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }
        return null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->findAll() as $user) {
            if ($user->username === $username) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): void
    {
        $data = json_decode(file_get_contents($this->dataFile), true) ?? [];
        $found = false;
        foreach ($data as &$item) {
            if ($item['id'] === $user->id) {
                $item['username'] = $user->username;
                $item['name'] = $user->name;
                $item['firstname'] = $user->firstname;
                $item['lastname'] = $user->lastname;
                $item['email'] = $user->email;
                $item['password'] = $user->password;
                $item['role'] = $user->role;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'password' => $user->password,
                'role' => $user->role,
            ];
        }
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}