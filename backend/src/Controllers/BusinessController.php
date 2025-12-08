<?php

namespace App\Controllers;

use Exception;
use App\Services\BusinessService;

class BusinessController
{
    private $businessService;

    public function __construct(BusinessService $businessService)
    {
        $this->businessService = $businessService;
    }

    public function create(int $user_id): void
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

            $response = $this->businessService->createBusiness(
                $inputData,
                $user_id,
            );

            if ($response) {
                http_response_code(201);
                echo json_encode([
                    "status" => "success",
                    "message" => "business successfully created",
                    "data" => $response,
                ]);
            } else {
                throw new Exception("Failed to create business");
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $decodedMessage = json_decode($message, true);
            $finalMessage = $decodedMessage ?? $message;
            $code = 500;
            if ($finalMessage === "Business not found") {
                $code = 404;
            } elseif (strpos($finalMessage, "Unauthorized") !== false) {
                $code = 403;
            } elseif (
                strpos($finalMessage, "Free Plan Limit Reached") !== false
            ) {
                $code = 400;
            } elseif ($decodedMessage) {
                $code = 400;
            }

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $finalMessage,
            ]);
        }
    }

    public function update(int $business_id, int $user_id)
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

            $response = $this->businessService->updateBusiness(
                $inputData,
                $business_id,
                $user_id,
            );

            if ($response) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "business successfully updated",
                    "data" => $response,
                ]);
            } else {
                throw new Exception("Failed to update business");
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

    public function getAll(int $userId): void
    {
        header("Content-Type: application/json");
        try {
            $data = $this->businessService->getAll($userId);

            echo json_encode([
                "status" => "success",
                "message" => "Successfully retrieved data",
                "data" => $data,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function getOne(int $businessId, int $userId): void
    {
        header("Content-Type: application/json");
        try {
            $data = $this->businessService->getById($businessId, $userId);

            echo json_encode([
                "status" => "success",
                "message" => "Successfully retrieved detail",
                "data" => $data,
            ]);
        } catch (Exception $e) {
            $code = $e->getMessage() === "Business not found" ? 404 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id, int $userId): void
    {
        header("Content-Type: application/json");
        try {
            $this->businessService->delete($id, $userId);

            echo json_encode([
                "status" => "success",
                "message" => "Successfully deleted",
            ]);
        } catch (Exception $e) {
            $code = $e->getMessage() === "Business not found" ? 404 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function getPublicByCode(string $code): void
    {
        header("Content-Type: application/json");

        try {
            $data = $this->businessService->getPublicBusinessByCode($code);

            echo json_encode([
                "status" => "success",
                "message" => "Business found",
                "data" => $data,
            ]);
        } catch (Exception $e) {
            // Jika tidak ketemu, return 404
            $code = $e->getMessage() === "Business not found" ? 404 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }
}
