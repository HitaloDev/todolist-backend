<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        protected Task $model
    ) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?Task
    {
        return $this->model->find($id);
    }

    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = $this->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException("Task com ID {$id} não encontrada");
        }

        $task->update($data);
        return $task->fresh();
    }

    public function delete(int $id): bool
    {
        $task = $this->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException("Task com ID {$id} não encontrada");
        }

        return $task->delete();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findByPriority(string $priority): Collection
    {
        return $this->model->where('priority', $priority)->get();
    }

    public function getOverdue(): Collection
    {
        return $this->model
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->get();
    }

    public function markAsCompleted(int $id): Task
    {
        $task = $this->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException("Task com ID {$id} não encontrada");
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $task->fresh();
    }
}