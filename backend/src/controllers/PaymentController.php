<?php

require_once __DIR__ . "/../services/PaymentService.php";

class PaymentController
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // POST /api/payment/create (Perlu Login)
    public function create(int $userId): void
    {
        try {
            // Harga paket membership
            $amount = 50000;
            $result = $this->paymentService->createPayment($userId, $amount);

            echo json_encode(["status" => "success", "data" => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
            ]);
        }
    }

    // POST /api/payment/notification (PUBLIC - Webhook)
    public function notification(): void
    {
        $json = file_get_contents("php://input");
        $notification = json_decode($json, true);

        if (!$notification) {
            http_response_code(400);
            return;
        }

        try {
            $this->paymentService->handleNotification($notification);
            http_response_code(200);
            echo "OK";
        } catch (Exception $e) {
            error_log("Webhook Error: " . $e->getMessage());
            http_response_code(500);
        }
    }
}
