<?php

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../models/FeedbackModel.php";

class FeedbackRepository
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

    public function create(FeedbackModel $feedback): ?FeedbackModel
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query =
                "INSERT INTO feedbacks (business_id, rating,text) VALUES (:business_id, :rating, :text)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(
                ":business_id",
                $feedback->business_id,
                PDO::PARAM_INT,
            );
            $stmt->bindParam(":rating", $feedback->rating, PDO::PARAM_INT);
            $stmt->bindParam(":text", $feedback->text, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $feedback->id = (int) $this->conn->lastInsertId();
                return $feedback;
            }
            return null;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }

    public function getAllByBusinessId(int $businessId): ?array
    {
        if ($this->conn === null) {
            return null;
        }

        try {
            $query = "SELECT * FROM feedbacks WHERE business_id = :business_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":business_id", $businessId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $feedbacks = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $feedbacks[] = new FeedbackModel(
                        $row["id"],
                        $row["business_id"],
                        $row["rating"],
                        $row["text"] ?? "",
                        $row["created_at"]
                            ? DateTimeImmutable::createFromFormat(
                                "Y-m-d H:i:s",
                                $row["created_at"],
                            )
                            : null,
                    );
                }
                return $feedbacks;
            }
            return null;
        } catch (PDOException $e) {
            error_log("[Query Error] " . $e->getMessage());
            return null;
        }
    }

    public function countTotalByUserId(int $userId): int
    {
        try {
            $query = "SELECT COUNT(f.id) as total
                          FROM feedbacks f
                          JOIN businesses b ON f.business_id = b.id
                          WHERE b.user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result["total"];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getWeeklyStats(int $userId): array
    {
        try {
            $query = "SELECT DATE(f.created_at) as date, COUNT(f.id) as count
                          FROM feedbacks f
                          JOIN businesses b ON f.business_id = b.id
                          WHERE b.user_id = :user_id
                          AND f.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          GROUP BY DATE(f.created_at)
                          ORDER BY date ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAverageRatingByUserId(int $userId): float
    {
        try {
            $query = "SELECT AVG(f.rating) as avg_rating
                          FROM feedbacks f
                          JOIN businesses b ON f.business_id = b.id
                          WHERE b.user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (float) ($result["avg_rating"] ?? 0);
        } catch (PDOException $e) {
            return 0.0;
        }
    }

    public function getRecentFeedbacksByUserId(
        int $userId,
        int $limit = 5,
    ): array {
        try {
            $query = "SELECT f.id, f.rating, f.text, f.created_at, b.name as business_name, b.location as business_location
                          FROM feedbacks f
                          JOIN businesses b ON f.business_id = b.id
                          WHERE b.user_id = :user_id
                          ORDER BY f.created_at DESC
                          LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
