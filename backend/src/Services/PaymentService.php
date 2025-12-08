<?php

namespace App\Services;

use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use App\Config\Config;
use Exception;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;

class PaymentService
{
    private $transactionRepo;
    private $userRepo;

    public function __construct(
        TransactionRepository $transactionRepo,
        UserRepository $userRepo,
    ) {
        $this->transactionRepo = $transactionRepo;
        $this->userRepo = $userRepo;

        MidtransConfig::$serverKey = Config::MIDTRANS_SERVER_KEY();
        MidtransConfig::$isProduction = Config::MIDTRANS_IS_PRODUCTION();
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    public function createPayment(int $userId, int $amount = 50000): object
    {
        $orderId = "MEM-" . $userId . "-" . time();

        $params = [
            "transaction_details" => [
                "order_id" => $orderId,
                "gross_amount" => $amount,
            ],
            "customer_details" => [
                "first_name" => "User ID",
                "last_name" => (string) $userId,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $this->transactionRepo->create(
                $userId,
                $orderId,
                $amount,
                $snapToken,
            );

            return (object) [
                "order_id" => $orderId,
                "token" => $snapToken,
                "redirect_url" =>
                    "https://app.sandbox.midtrans.com/snap/v2/vtweb/" .
                    $snapToken,
            ];
        } catch (Exception $e) {
            throw new Exception("Midtrans Error: " . $e->getMessage());
        }
    }

    public function handleNotification(array $notification): void
    {
        $transactionStatus = $notification["transaction_status"];
        $orderId = $notification["order_id"];
        $fraudStatus = $notification["fraud_status"] ?? "";

        $transaction = $this->transactionRepo->findByOrderId($orderId);
        if (!$transaction) {
            return;
        }

        $status = "pending";

        if ($transactionStatus == "capture") {
            $status = $fraudStatus == "challenge" ? "challenge" : "success";
        } elseif ($transactionStatus == "settlement") {
            $status = "success";
        } elseif (
            $transactionStatus == "deny" ||
            $transactionStatus == "expire" ||
            $transactionStatus == "cancel"
        ) {
            $status = "failed";
        }

        $this->transactionRepo->updateStatus($orderId, $status);
        if ($status == "success" && $transaction["status"] != "success") {
            $userId = $transaction["user_id"];
            $this->userRepo->extendMembership($userId, 30);
        }
    }
}
