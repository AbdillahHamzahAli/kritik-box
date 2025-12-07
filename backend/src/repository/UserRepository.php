<?php

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../models/UserModel.php";

class UserRepository
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

    public function create(UserModel $user): ?UserModel
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query =
                "INSERT INTO users (name, username,email, password) VALUES (:name, :username, :email, :password)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":name", $user->name, PDO::PARAM_STR);
            $stmt->bindParam(":username", $user->username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $user->email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $user->password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $user->id = (int) $this->conn->lastInsertId();
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }
    public function findByEmail(string $email): ?UserModel
    {
        try {
            $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new UserModel(
                    $row["id"],
                    $row["email"],
                    $row["name"],
                    $row["username"],
                    $row["password"],
                    $row["membership_expires_at"],
                );
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }
    public function findById(int $id): ?UserModel
    {
        try {
            $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return new UserModel(
                    $row["id"],
                    $row["name"],
                    $row["username"],
                    $row["email"],
                    $row["password"],
                    $row["membership_expires_at"],
                    $row["created_at"],
                );
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }
    public function extendMembership(int $userId, int $days): bool
    {
        try {
            $queryCheck =
                "SELECT membership_expires_at FROM users WHERE id = :id";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(":id", $userId);
            $stmtCheck->execute();
            $currentData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            $baseDate = new DateTime();
            if ($currentData && !empty($currentData["membership_expires_at"])) {
                $expiryDate = new DateTime(
                    $currentData["membership_expires_at"],
                );
                $now = new DateTime();
                if ($expiryDate > $now) {
                    $baseDate = $expiryDate;
                }
            }

            $baseDate->modify("+$days days");
            $newExpiry = $baseDate->format("Y-m-d H:i:s");

            $query =
                "UPDATE users SET membership_expires_at = :expiry WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":expiry", $newExpiry);
            $stmt->bindParam(":id", $userId);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[Membership Error] " . $e->getMessage());
            return false;
        }
    }
}
