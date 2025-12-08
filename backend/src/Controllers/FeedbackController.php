<?php

namespace App\Controllers;

use Exception;
use App\Services\FeedbackService;

class FeedbackController
{
    private $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    public function create(string $uniqueCode): void
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

            $response = $this->feedbackService->createFeedback(
                $inputData,
                $uniqueCode,
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
            $code = $decodedMessage ? 401 : 500;

            http_response_code($code);
            echo json_encode([
                "status" => "error",
                "message" => $finalMessage,
            ]);
        }
    }

    public function getAll(int $business_id, int $user_id): void
    {
        header("Content-Type: application/json");

        try {
            $feedbacks = $this->feedbackService->getAll($business_id, $user_id);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "feedbacks retrieved successfully",
                "data" => $feedbacks,
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
}
