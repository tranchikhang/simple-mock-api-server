<?php

/**
 * constant
 */
DEFINE('JSON_HEADER', 'Content-Type: application/json; charset=utf-8');
DEFINE('LOG_FOLDER', __DIR__ . '/logs/');
DEFINE('CONFIG_FILE', 'api.json');

/**
 * handle request and return response based on config file
 * @param  string $request request path
 * @param  array $routes pre-defined response
 * @return HTTP response
 */
function handleRequest($request, $routes) {
    $route = isset($routes[$request]) ? $routes[$request] : null;
    if ($route) {
        // return json directly
        http_response_code($route['responseCode']);
        header(JSON_HEADER);
        echo json_encode($route['responseData'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        header(JSON_HEADER);
        echo json_encode(['error' => 'Not found'], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * write data to log file
 * @param  array  $args not used for now :/
 * @return void nope, just write to file
 */
function writeLog($args = []) {
    // get time based on the time format
    $date_time = date('Y-m-d H:i:s');

    $request_info = [
        'RequestMethod' => $_SERVER['REQUEST_METHOD'],
        'RequestURI' => $_SERVER['REQUEST_URI'],
        'Headers' => getallheaders(),
        'Body' => file_get_contents('php://input'),
        'QueryParams' => $_GET,
        'PostParams' => $_POST,
    ];
    $log_file_name = LOG_FOLDER . date('Y-m-d') . '.log';
    $log_message = '[' . $date_time . ']';
    $log_content = json_encode($request_info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    $log_message .= ': ' . $log_content;

    file_put_contents($log_file_name, $log_message, FILE_APPEND);
}

if (!file_exists(LOG_FOLDER)) {
    mkdir(LOG_FOLDER);
}

// log setup
// need refactor ;(
// also need to setup timezone!
$files = glob(LOG_FOLDER . '*.log');
foreach ($files as $file) {
    if (filemtime($file) < strtotime('-5 days')) {
        unlink($file);
    }
}

// write request to log, also need refactor ;(
writeLog();

$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', $_SERVER['REQUEST_URI']);
$path = array_pop($path);

// load config file
$config = json_decode(file_get_contents(CONFIG_FILE), true);
$headers = getallheaders();

switch ($method) {
    case "GET":
        // Handle GET request
        handleRequest($path, $config['routes']['GET']);
        break;
    case "POST":
        // Handle POST request, not tested ;(
        handleRequest($path, $config['routes']['POST']);
        break;
    case "PUT":
        // Handle PUT request, not tested ;(
        handleRequest($path, $config['routes']['PUT']);
        break;
    case "DELETE":
        // Handle DELETE request, not tested ;(
        handleRequest($path, $config['routes']['DELETE']);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
}



