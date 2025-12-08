<?php

namespace App\Controllers;

use App\Services\UserService;
use Exception;

class UsersController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(): void
    {
        header("Content-Type: application/json");

        try {
            $inputData = json_decode(file_get_contents("php://input"), true);

            if (!$inputData) {
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid JSON Input",
                ]);
                return;
            }

            $createdUser = $this->userService->createUser($inputData);

            if ($createdUser) {
                http_response_code(201);
                echo json_encode([
                    "status" => "success",
                    "message" => "User successfully created",
                    "data" => $createdUser,
                ]);
            } else {
                throw new Exception("Failed to create user");
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $decodedMessage = json_decode($message, true);
            $finalMessage = $decodedMessage ?? $message;
            $code = $decodedMessage ? 401 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $finalMessage,
            ]);
        }
    }

    public function login(): void
    {
        header("Content-Type: application/json");
        $input = json_decode(file_get_contents("php://input"), true);

        try {
            $result = $this->userService->login($input);

            echo json_encode([
                "status" => "success",
                "message" => "Login berhasil",
                "data" => $result,
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $decodedMessage = json_decode($message, true);
            $finalMessage = $decodedMessage ?? $message;
            $code = $decodedMessage ? 401 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $finalMessage,
            ]);
        }
    }

    public function me(int $userId): void
    {
        header("Content-Type: application/json");

        try {
            $profile = $this->userService->getProfile($userId);

            echo json_encode([
                "status" => "success",
                "message" => "Profile retrieved",
                "data" => $profile,
            ]);
        } catch (Exception $e) {
            $code = $e->getMessage() === "User not found" ? 404 : 500;
            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    // public function getAllUser() {}
    // public function getUserById($id) {}
    // public function editUserData($id) {}
    // public function deleteUserById($id) {}
}
