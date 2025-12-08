<?php

namespace App\Services;

use Exception;
use DateTime;
use App\Repository\BusinessRepository;
use App\Repository\FeedbackRepository;
use App\Repository\UserRepository;

class DashboardService
{
    private $businessRepo;
    private $feedbackRepo;
    private $userRepo;

    public function __construct(
        BusinessRepository $businessRepo,
        FeedbackRepository $feedbackRepo,
        UserRepository $userRepository,
    ) {
        $this->businessRepo = $businessRepo;
        $this->feedbackRepo = $feedbackRepo;
        $this->userRepo = $userRepository;
    }

    public function getStats(int $userId): array
    {
        $totalBusinesses = $this->businessRepo->countByUserId($userId);
        $totalFeedbacks = $this->feedbackRepo->countTotalByUserId($userId);

        $rawAvgRating = $this->feedbackRepo->getAverageRatingByUserId($userId);
        $avgRating = round($rawAvgRating, 1);

        $rawWeeklyStats = $this->feedbackRepo->getWeeklyStats($userId);
        $chartData = $this->fillMissingDates($rawWeeklyStats);

        $recentFeedbacks = $this->feedbackRepo->getRecentFeedbacksByUserId(
            $userId,
            5,
        );

        $user = $this->userRepo->findById($userId);

        if (!$user) {
            throw new Exception("User not found");
        }

        $isPremium = false;

        if (!empty($user->membership_expires_at)) {
            $expiryDate = new DateTime($user->membership_expires_at);
            $now = new DateTime();
            if ($expiryDate > $now) {
                $isPremium = true;
            }
        }
        return [
            "summary" => [
                "total_businesses" => $totalBusinesses,
                "total_feedbacks" => $totalFeedbacks,
                "average_rating" => $avgRating,
                "is_premium" => $isPremium,
            ],
            "chart" => $chartData,
            "recent_feedbacks" => $recentFeedbacks,
        ];
    }

    private function fillMissingDates(array $rawData): array
    {
        $mappedData = [];
        foreach ($rawData as $row) {
            $mappedData[$row["date"]] = (int) $row["count"];
        }

        $finalData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date("Y-m-d", strtotime("-$i days"));

            $finalData[] = [
                "date" => $date,
                "count" => $mappedData[$date] ?? 0,
            ];
        }

        return $finalData;
    }
}
