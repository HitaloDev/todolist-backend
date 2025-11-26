<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{

    public function all(): Collection;

    public function paginate(int $perPage = 15);

    public function find(int $id): ?Task;

    public function create(array $data): Task;

    public function update(int $id, array $data): Task;

    public function delete(int $id): bool;

    public function findByStatus(string $status): Collection;

    public function findByPriority(string $priority): Collection;

    public function getOverdue(): Collection;

    public function markAsCompleted(int $id): Task;
}