<?php

require_once __DIR__ . "/../services/DashboardService.php";

class DashboardController
{
    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(int $userId): void
    {
        header("Content-Type: application/json");
        try {
            $data = $this->dashboardService->getStats($userId);

            echo json_encode([
                "status" => "success",
                "message" => "Dashboard stats retrieved",
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
}
