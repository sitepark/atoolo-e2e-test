<?php

declare(strict_types=1);

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}


$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$body = file_get_contents('php://input');

$routes = [
    [
        'method' => 'POST',
        'path'   => '#^/api/graphql#',
        'body'   => '#.*mutation authenticate.*#',
        'handler' => static function ($matches) {
            json_response([
                'data' => [
                    'security' => [
                        'authenticate' => [
                            'withPassword' => [
                                'status' => 'SUCCESS',
                                'user' => [
                                    'id' => '123',
                                    'username' => 'peterpan',
                                    'firstName' => 'Peter',
                                    'lastName' => 'Pan',
                                    'email' => 'peterpan@neverland.com',
                                    'roles' => ['A', 'B'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        },
    ],
];

foreach ($routes as $route) {
    if (
        $method === $route['method']
        && preg_match($route['path'], $path, $matches)
        && ($route['body'] === null || preg_match($route['body'], $body))
    ) {
        $route['handler']($matches);
        exit;
    }
}

// Fallback 404
http_response_code(404);
echo "Not Found " . $path;
