<?php

// Load dependencies
use App\Repository\UserRepository;
use App\Repository\BusinessRepository;
use App\Repository\FeedbackRepository;
use App\Repository\TransactionRepository;
use App\Services\UserService;
use App\Services\BusinessService;
use App\Services\FeedbackService;
use App\Services\PaymentService;
use App\Services\DashboardService;
use App\Controllers\UsersController;
use App\Controllers\BusinessController;
use App\Controllers\FeedbackController;
use App\Controllers\PaymentController;
use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;

$authMiddleware = new AuthMiddleware();

$userRepository = new UserRepository();
$businessRepository = new BusinessRepository();
$feedbackRepository = new FeedbackRepository();
$transactionRepository = new TransactionRepository();

$userService = new UserService($userRepository, $businessRepository);
$businessService = new BusinessService($businessRepository, $userRepository);
$feedbackService = new FeedbackService(
    $feedbackRepository,
    $businessRepository,
);
$paymentService = new PaymentService($transactionRepository, $userRepository);
$dashboardService = new DashboardService(
    $businessRepository,
    $feedbackRepository,
    $userRepository,
);

$usersController = new UsersController($userService);
$businessesController = new BusinessController($businessService);
$feedbacksController = new FeedbackController($feedbackService);
$paymentController = new PaymentController($paymentService);
$dashboardController = new DashboardController($dashboardService);

// --- ROUTING ---
$requestUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

function sendResponse405()
{
    http_response_code(405);
    header("Content-Type: application/json");
    echo json_encode(["message" => "Method Not Allowed"]);
}

if ($requestUri == "/") {
    if ($requestMethod === "GET") {
        echo "API Root Ready";
    } else {
        sendResponse405();
    }
} elseif ($requestUri == "/api/register" && $requestMethod == "POST") {
    $usersController->register();
} elseif ($requestUri == "/api/login" && $requestMethod == "POST") {
    $usersController->login();
} elseif ($requestUri == "/api/me" && $requestMethod == "GET") {
    $user = $authMiddleware->handle();
    $usersController->me($user->id);
} elseif ($requestUri == "/api/business") {
    $user = $authMiddleware->handle();
    switch ($requestMethod) {
        case "POST":
            $businessesController->create($user->id);
            break;
        case "GET":
            $businessesController->getAll($user->id);
            break;
        default:
            sendResponse405();
            break;
    }
} elseif (preg_match('#^/api/business/(\d+)$#', $requestUri, $matches)) {
    $businessId = (int) $matches[1];
    $user = $authMiddleware->handle();
    switch ($requestMethod) {
        case "GET":
            $businessesController->getOne($businessId, $user->id);
            break;
        case "PUT":
            $businessesController->update($businessId, $user->id);
            break;
        case "DELETE":
            $businessesController->delete($businessId, $user->id);
            break;
        default:
            sendResponse405();
            break;
    }
} elseif (
    preg_match('#^/api/business/(\d+)/feedback$#', $requestUri, $matches) &&
    $requestMethod == "GET"
) {
    $businessId = (int) $matches[1];
    $user = $authMiddleware->handle();
    $feedbacksController->getAll($businessId, $user->id);
} elseif (
    preg_match('#^/api/public/business/([A-Z0-9]+)$#', $requestUri, $matches)
) {
    $uniqueCode = $matches[1];
    if ($requestMethod === "GET") {
        $businessesController->getPublicByCode($uniqueCode);
    } else {
        sendResponse405();
    }
} elseif (
    preg_match('#^/api/feedback/([A-Z0-9]+)$#', $requestUri, $matches) &&
    $requestMethod == "POST"
) {
    $code = $matches[1];
    $feedbacksController->create($code);
} elseif ($requestUri == "/api/payment/create" && $requestMethod == "POST") {
    $user = $authMiddleware->handle();
    $paymentController->create($user->id);
} elseif (
    $requestUri == "/api/payment/notification" &&
    $requestMethod == "POST"
) {
    $paymentController->notification();
} elseif ($requestUri == "/api/dashboard" && $requestMethod == "GET") {
    $user = $authMiddleware->handle();
    $dashboardController->index($user->id);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Route Not Found"]);
}
