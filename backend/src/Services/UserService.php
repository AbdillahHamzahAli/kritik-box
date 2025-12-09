<?php

namespace App\Services;

use App\Config\Config;
use DateTime;
use Exception;
use Firebase\JWT\JWT;
use BlakvGhost\PHPValidator\Validator;

use App\Repository\UserRepository;
use App\Repository\BusinessRepository;
use App\Models\UserModel;

class UserService
{
    private $userRepository;
    private $businessRepository;

    public function __construct(
        UserRepository $userRepository,
        BusinessRepository $businessRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->businessRepository = $businessRepository;
    }

    public function createUser(array $data): object
    {
        $validator = new Validator($data, [
            "email" => "required|email",
            "password" => "required|min:6",
            "name" => "required",
            "username" => "required",
        ]);

        if (!$validator->isValid()) {
            throw new Exception(json_encode($validator->getErrors()));
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            throw new Exception('Email sudah terdaftar');
        }

        $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
        $user = new UserModel(
            null,
            $data["name"],
            $data["username"],
            $data["email"],
            $hashedPassword,
        );

        $createdUser = $this->userRepository->create($user);
        if (!$createdUser) {
            throw new Exception("Gagal membuat user di database");
        }

        return (object) [
            "id" => $createdUser->id,
            "name" => $createdUser->name,
            "username" => $createdUser->username,
            "email" => $createdUser->email,
        ];
    }
    public function login(array $data): object
    {
        $validator = new Validator($data, [
            "email" => "required|email",
            "password" => "required|min:6",
        ]);

        if (!$validator->isValid()) {
            throw new Exception(json_encode($validator->getErrors()));
        }

        $user = $this->userRepository->findByEmail($data["email"]);

        if (!$user || !password_verify($data["password"], $user->password)) {
            throw new Exception("Invalid email or password");
        }

        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "iss" => "localhost",
            "data" => [
                "id" => $user->id,
                "email" => $user->email,
            ],
        ];

        $jwt = JWT::encode($payload, Config::JWT_SECRET(), Config::JWT_ALGO());

        return (object) [
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
            ],
            "token" => $jwt,
        ];
    }
    public function getProfile(int $userId): object
    {
        $user = $this->userRepository->findById($userId);
        $countBusinesses = $this->businessRepository->countByUserId($userId);

        if (!$user) {
            throw new Exception("User not found");
        }

        $isPremium = false;
        if (!empty($user->membership_expires_at)) {
            $expiryDate = new DateTime($user->membership_expires_at);
            $now = new DateTime();
            if ($expiryDate > $now) {
                $isPremium = true;
            }
        }

        return (object) [
            "id" => $user->id,
            "name" => $user->name,
            "username" => $user->username,
            "email" => $user->email,
            "count_businesses" => $countBusinesses,
            "is_premium" => $isPremium,
            "membership_expires_at" => $user->membership_expires_at,
            "joined_at" => $user->created_at ?? null,
        ];
    }
}
