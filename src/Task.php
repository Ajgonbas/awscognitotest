<?php

namespace ApexApi;

class Task
{
    private $userId;
    private $tasksFile = 'tasks_db.csv';
    private $tasks = [];

    public function __construct()
    {
        $this->userId = $_SESSION['user'];
        $this->loadTasks();
    }

    public function handle($request)
    {
        $action = $request['action'] ?? null;
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($action) {
            case 'list':
                if ($method !== 'GET') {
                    http_response_code(405); // Method Not Allowed
                    return json_encode(['error' => 'Method not allowed for listing tasks']);
                }
                return $this->listTasks();

            case 'add':
                if ($method !== 'POST') {
                    http_response_code(405); // Method Not Allowed
                    return json_encode(['error' => 'Method not allowed for adding tasks']);
                }
                return $this->addTask($request);

            case 'mark':
                if ($method !== 'PUT') {
                    http_response_code(405); // Method Not Allowed
                    return json_encode(['error' => 'Method not allowed for marking tasks']);
                }
                $putVars = json_decode(file_get_contents("php://input"), true);
                return $this->markTaskCompleted($putVars);
            default:
                http_response_code(400); // Bad Request
                return json_encode(['error' => 'Invalid action']);
        }
    }

    private function listTasks()
    {
        return json_encode($this->tasks);
    }

    private function addTask($request)
    {
        if (!isset($request['description']) || empty($request['description'])) {
            http_response_code(400); // Bad Request
            return json_encode(['error' => 'Task description is required']);
        }

        $newId = $this->getNextId();
        $task = [$newId, $request['description'], 0, $this->userId];

        // Append the new task to the file
        $file = fopen($this->tasksFile, 'a');
        fputcsv($file, $task);
        fclose($file);

        // Update the in-memory task list
        $this->tasks[] = $task;

        return json_encode(['id' => $newId]);
    }

    private function markTaskCompleted($request)
    {
        if (!isset($request['id'])) {
            http_response_code(400); // Bad Request
            return json_encode(['error' => 'Task ID is required']);
        }

        $found = false;

        foreach ($this->tasks as &$task) {
            if ($task[0] == $request['id']) {
                $task[2] = 1; // Mark as completed
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->overwriteTasks();
            return json_encode(['status' => 'Task marked as completed']);
        } else {
            http_response_code(404); // Not Found
            return json_encode(['error' => 'Task not found']);
        }
    }

    private function loadTasks()
    {
        if (file_exists($this->tasksFile)) {
            $file = fopen($this->tasksFile, 'r');
            while (($line = fgetcsv($file)) !== false) {
                if ($line[3] === $this->userId) {
                    $this->tasks[] = $line;
                }
            }
            fclose($file);
        }
    }

    private function getNextId()
    {
        $lastId = 0;
        if (!empty($this->tasks)) {
            $lastId = (int)end($this->tasks)[0];
        }
        return $lastId + 1;
    }

    private function overwriteTasks()
    {
        $file = fopen($this->tasksFile, 'w');
        foreach ($this->tasks as $task) {
            fputcsv($file, $task);
        }
        fclose($file);
    }
}