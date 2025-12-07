<?php
require_once __DIR__ . "/../../config/Database.php";

class TransactionRepository
{
    private $conn;

    public function __construct()
    {
        try {
            $db = new Database();
            $this->conn = $db->connect();
        } catch (Exception $e) {
            $this->conn = null;
            error_log("[DB Error] " . $e->getMessage());
        }
    }

    public function create($userId, $orderId, $amount, $snapToken): bool
    {
        $query = "INSERT INTO transactions (user_id, order_id, amount, snap_token, status)
                  VALUES (:uid, :oid, :amt, :token, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $userId);
        $stmt->bindParam(":oid", $orderId);
        $stmt->bindParam(":amt", $amount);
        $stmt->bindParam(":token", $snapToken);
        return $stmt->execute();
    }

    public function updateStatus($orderId, $status): bool
    {
        $query =
            "UPDATE transactions SET status = :status WHERE order_id = :oid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":oid", $orderId);
        return $stmt->execute();
    }

    public function findByOrderId($orderId)
    {
        $query = "SELECT * FROM transactions WHERE order_id = :oid LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":oid", $orderId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
