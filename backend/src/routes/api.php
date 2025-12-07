<?php

// Load dependencies
require_once __DIR__ . "/../repository/UserRepository.php";
require_once __DIR__ . "/../services/UserService.php";
require_once __DIR__ . "/../controllers/UsersController.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";
require_once __DIR__ . "/../repository/BusinessRepository.php";
require_once __DIR__ . "/../services/BusinessService.php";
require_once __DIR__ . "/../controllers/BusinessController.php";
require_once __DIR__ . "/../repository/FeedbackRepository.php";
require_once __DIR__ . "/../services/FeedbackService.php";
require_once __DIR__ . "/../controllers/FeedbackController.php";
require_once __DIR__ . "/../repository/TransactionRepository.php";
require_once __DIR__ . "/../services/PaymentService.php";
require_once __DIR__ . "/../controllers/PaymentController.php";
require_once __DIR__ . "/../services/DashboardService.php";
require_once __DIR__ . "/../controllers/DashboardController.php";

// --- USER ---
$userRepository = new UserRepository();
$userService = new UserService($userRepository);
$usersController = new UsersController($userService);
$authMiddleware = new AuthMiddleware();

// --- BUSINESS ---
$businessRepository = new BusinessRepository();
$businessService = new BusinessService($businessRepository, $userRepository);
$businessesController = new BusinessController($businessService);

// --- FEEDBACK ---
$feedbackRepository = new FeedbackRepository();
$feedbackService = new FeedbackService(
    $feedbackRepository,
    $businessRepository,
);
$feedbacksController = new FeedbackController($feedbackService);

// --- PAYMENT ---
$transactionRepository = new TransactionRepository();
$paymentService = new PaymentService($transactionRepository, $userRepository);
$paymentController = new PaymentController($paymentService);

// --- DASHBOARD ---
$dashboardService = new DashboardService(
    $businessRepository,
    $feedbackRepository,
);
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
