<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $repository
    ) {}

    public function getAllTasks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getTaskById(int $id): ?Task
    {
        return $this->repository->find($id);
    }

    public function createTask(array $data): Task
    {
        return $this->repository->create($data);
    }

    public function updateTask(int $id, array $data): Task
    {
        if (isset($data['status']) && $data['status'] === 'completed' && !isset($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        if (isset($data['status']) && $data['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        return $this->repository->update($id, $data);
    }

    public function deleteTask(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getTasksByStatus(string $status): Collection
    {
        $validStatuses = ['pending', 'in_progress', 'completed'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status inválido: {$status}");
        }

        return $this->repository->findByStatus($status);
    }

    public function getTasksByPriority(string $priority): Collection
    {
        $validPriorities = ['low', 'medium', 'high'];
        
        if (!in_array($priority, $validPriorities)) {
            throw new \InvalidArgumentException("Prioridade inválida: {$priority}");
        }

        return $this->repository->findByPriority($priority);
    }

    public function getOverdueTasks(): Collection
    {
        return $this->repository->getOverdue();
    }

    public function completeTask(int $id): Task
    {
        return $this->repository->markAsCompleted($id);
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->repository->all()->count(),
            'pending' => $this->repository->findByStatus('pending')->count(),
            'in_progress' => $this->repository->findByStatus('in_progress')->count(),
            'completed' => $this->repository->findByStatus('completed')->count(),
            'overdue' => $this->repository->getOverdue()->count(),
        ];
    }

    public function filterTasks(array $filters): Collection
    {
        $query = $this->repository->all();

        if (isset($filters['status'])) {
            $query = $this->repository->findByStatus($filters['status']);
        }

        if (isset($filters['priority'])) {
            $query = $query->where('priority', $filters['priority']);
        }

        if (isset($filters['overdue']) && $filters['overdue']) {
            $query = $query->filter(function ($task) {
                return $task->isOverdue();
            });
        }

        return $query;
    }
}