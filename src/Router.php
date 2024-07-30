<?php

namespace ApexApi;

class Router
{
    public function route($request)
    {
        if (!isset($request['resource'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Resource not specified']);
            return;
        }

        $resource = $request['resource'];

        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user'];

        switch ($resource) {
            case 'task':
                $task = new Task($userId);
                echo $task->handle($request);
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid resource']);
        }
    }
}