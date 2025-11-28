<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidTaskStatusException;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}


    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $tasks = $this->taskService->getAllTasks($perPage);

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tasks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task criada com sucesso',
                'data' => $task,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task não encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Task atualizada com sucesso',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return response()->json([
                'success' => true,
                'message' => 'Task deletada com sucesso',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function complete(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->completeTask($id);

            return response()->json([
                'success' => true,
                'message' => 'Task marcada como completa',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao completar task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function byStatus(string $status): JsonResponse
    {
        try {
            $tasks = $this->taskService->getTasksByStatus($status);

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (InvalidTaskStatusException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tasks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function byPriority(string $priority): JsonResponse
    {
        try {
            $tasks = $this->taskService->getTasksByPriority($priority);

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tasks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function overdue(): JsonResponse
    {
        try {
            $tasks = $this->taskService->getOverdueTasks();

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tasks atrasadas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->taskService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}