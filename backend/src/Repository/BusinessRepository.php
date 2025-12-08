<?php

namespace App\Repository;

use App\Config\Database;
use App\Models\BusinessModel;
use Exception;
use PDO;
use PDOException;

class BusinessRepository
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

    public function create(BusinessModel $business): ?BusinessModel
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query =
                "INSERT INTO businesses (user_id, name, location, address, qrcode) VALUES (:user_id, :name, :location, :address, :qrcode)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":user_id", $business->user_id, PDO::PARAM_INT);
            $stmt->bindParam(":name", $business->name, PDO::PARAM_STR);
            $stmt->bindParam(":location", $business->location, PDO::PARAM_STR);
            $stmt->bindParam(":address", $business->address, PDO::PARAM_STR);
            $stmt->bindParam(":qrcode", $business->qrcode, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $business->id = (int) $this->conn->lastInsertId();
                return $business;
            }
            return null;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }

    public function update(BusinessModel $business): bool
    {
        try {
            $query = "UPDATE businesses
                          SET name = :name,
                              location = :location,
                              address = :address,
                              qrcode = :qrcode
                          WHERE id = :id AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":id", $business->id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $business->user_id, PDO::PARAM_INT);
            $stmt->bindParam(":name", $business->name, PDO::PARAM_STR);
            $stmt->bindParam(":location", $business->location, PDO::PARAM_STR);
            $stmt->bindParam(":address", $business->address, PDO::PARAM_STR);
            $stmt->bindParam(":qrcode", $business->qrcode, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[Update Error] " . $e->getMessage());
            return false;
        }
    }

    public function findByUserId(int $userId): ?array
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query =
                "SELECT * FROM businesses WHERE user_id = :user_id ORDER BY created_at DESC";

            if ($this->conn === null) {
                return [];
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();

            $businesses = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $businesses[] = new BusinessModel(
                    (int) $row["id"],
                    (int) $row["user_id"],
                    $row["name"],
                    $row["location"],
                    $row["address"] ?? "",
                    $row["qrcode"] ?? "",
                );
            }

            return $businesses;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?BusinessModel
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query = "SELECT * FROM businesses WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    return new BusinessModel(
                        (int) $row["id"],
                        (int) $row["user_id"],
                        $row["name"],
                        $row["location"],
                        $row["address"] ?? "",
                        $row["qrcode"] ?? "",
                    );
                }
            }
            return null;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): bool
    {
        if ($this->conn === null) {
            return false;
        }

        try {
            $query = "DELETE FROM businesses WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return false;
        }
    }

    public function countByUserId(int $userId): int
    {
        if ($this->conn === null) {
            return 0;
        }

        try {
            $query = "SELECT COUNT(*) FROM businesses WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return 0;
        }
    }
    public function isQrCodeExists(string $code): bool
    {
        try {
            $query =
                "SELECT COUNT(*) as total FROM businesses WHERE qrcode = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":code", $code);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row["total"] > 0;
        } catch (PDOException $e) {
            return true;
        }
    }

    public function findByQrCode(string $code): ?BusinessModel
    {
        try {
            $query = "SELECT * FROM businesses WHERE qrcode = :code LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":code", $code);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new BusinessModel(
                    (int) $row["id"],
                    (int) $row["user_id"],
                    $row["name"],
                    $row["location"],
                    $row["address"] ?? "",
                    $row["qrcode"],
                );
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
