<?php

require_once __DIR__ . "/../repository/BusinessRepository.php";
require_once __DIR__ . "/../repository/FeedbackRepository.php";

class DashboardService
{
    private $businessRepo;
    private $feedbackRepo;

    public function __construct(
        BusinessRepository $businessRepo,
        FeedbackRepository $feedbackRepo,
    ) {
        $this->businessRepo = $businessRepo;
        $this->feedbackRepo = $feedbackRepo;
    }

    public function getStats(int $userId): array
    {
        $totalBusinesses = $this->businessRepo->countByUserId($userId);
        $totalFeedbacks = $this->feedbackRepo->countTotalByUserId($userId);

        $rawAvgRating = $this->feedbackRepo->getAverageRatingByUserId($userId);
        $avgRating = round($rawAvgRating, 1);

        $rawWeeklyStats = $this->feedbackRepo->getWeeklyStats($userId);
        $chartData = $this->fillMissingDates($rawWeeklyStats);

        return [
            "summary" => [
                "total_businesses" => $totalBusinesses,
                "total_feedbacks" => $totalFeedbacks,
                "average_rating" => $avgRating,
            ],
            "chart" => $chartData,
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
