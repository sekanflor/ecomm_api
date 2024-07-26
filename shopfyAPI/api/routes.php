<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

require_once("./config/database.php");
require_once("./modules/post.php");
require_once("./modules/get.php");

$conn = new Connection();
$pdo = $conn->connect();
$post = new Post($pdo);
$get = new Get($pdo);

if (isset($_REQUEST['request'])) {
    $request = explode('/', trim($_REQUEST['request'], '/'));
} else {
    http_response_code(404);
    echo json_encode(["error" => "Not Found"]);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid JSON data"]);
            break;
        }

        switch ($request[0]) {
            case 'signup':
                echo json_encode($post->signup($data));
                break;

            case 'login':
                echo json_encode($post->login($data));
                break;
            case 'add_order':
                echo json_encode($post->createOrder($data));
                break;
            case 'delete_order':
                    echo json_encode($post->deleteOrder($data));
                    break;
            case 'pay_order':
                        echo json_encode($post->payOutOrder($data));
                        break;
            default:
                http_response_code(403);
                echo json_encode(["error" => "Forbidden"]);
                break;
        }
        break;

    case 'GET':
        switch ($request[0]) {
            case 'items':
                echo json_encode($get->fetchItems());
                break;
            case 'orders':
                if (count($request) > 1) {
                    echo json_encode($get->fetchOrders($request[1]));
                } else {
                    echo json_encode($get->fetchOrders());
                }
                break;
            default:
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
                break;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
        break;
}
?>
