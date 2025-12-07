<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use BlakvGhost\PHPValidator\Validator;
use BlakvGhost\PHPValidator\ValidatorException;

require_once __DIR__ . "/../../config/Config.php";
require_once __DIR__ . "/../repository/UserRepository.php";
require_once __DIR__ . "/../models/UserModel.php";

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
}
