<?php

namespace Tests\Unit;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TaskRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TaskRepository(new Task());
    }

    /** @test */
    public function it_can_get_all_tasks()
    {
        Task::factory()->count(5)->create();

        $tasks = $this->repository->all();

        $this->assertCount(5, $tasks);
    }

    /** @test */
    public function it_can_paginate_tasks()
    {
        Task::factory()->count(20)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(20, $paginated->total());
    }

    /** @test */
    public function it_orders_tasks_by_created_at_desc_when_paginating()
    {
        $oldTask = Task::factory()->create(['created_at' => now()->subDays(2)]);
        $newTask = Task::factory()->create(['created_at' => now()]);

        $paginated = $this->repository->paginate(10);

        $this->assertEquals($newTask->id, $paginated->first()->id);
    }

    /** @test */
    public function it_can_find_task_by_id()
    {
        $task = Task::factory()->create(['title' => 'Task Específica']);

        $found = $this->repository->find($task->id);

        $this->assertNotNull($found);
        $this->assertEquals('Task Específica', $found->title);
    }

    /** @test */
    public function it_returns_null_when_task_not_found()
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_create_task()
    {
        $data = [
            'title' => 'Nova Task',
            'description' => 'Descrição',
            'status' => 'pending',
            'priority' => 'high',
        ];

        $task = $this->repository->create($data);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Nova Task', $task->title);
        $this->assertDatabaseHas('tasks', ['title' => 'Nova Task']);
    }

    /** @test */
    public function it_can_update_task()
    {
        $task = Task::factory()->create([
            'title' => 'Título Original',
            'status' => 'pending',
        ]);

        $updated = $this->repository->update($task->id, [
            'title' => 'Título Atualizado',
            'status' => 'completed',
        ]);

        $this->assertEquals('Título Atualizado', $updated->title);
        $this->assertEquals('completed', $updated->status);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Atualizado',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_updating_non_existent_task()
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task com ID 999 não encontrada');

        $this->repository->update(999, ['title' => 'Test']);
    }

    /** @test */
    public function it_can_delete_task()
    {
        $task = Task::factory()->create();

        $result = $this->repository->delete($task->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_throws_exception_when_deleting_non_existent_task()
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task com ID 999 não encontrada');

        $this->repository->delete(999);
    }

    /** @test */
    public function it_can_find_tasks_by_status()
    {
        Task::factory()->count(3)->create(['status' => 'pending']);
        Task::factory()->count(2)->create(['status' => 'completed']);

        $pendingTasks = $this->repository->findByStatus('pending');

        $this->assertCount(3, $pendingTasks);
        $pendingTasks->each(function ($task) {
            $this->assertEquals('pending', $task->status);
        });
    }

    /** @test */
    public function it_can_find_tasks_by_priority()
    {
        Task::factory()->count(4)->create(['priority' => 'high']);
        Task::factory()->count(2)->create(['priority' => 'low']);

        $highPriorityTasks = $this->repository->findByPriority('high');

        $this->assertCount(4, $highPriorityTasks);
        $highPriorityTasks->each(function ($task) {
            $this->assertEquals('high', $task->priority);
        });
    }

    /** @test */
    public function it_can_get_overdue_tasks()
    {
        Task::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);
        Task::factory()->create([
            'due_date' => now()->subDays(2),
            'status' => 'in_progress',
        ]);
        Task::factory()->create([
            'due_date' => now()->subDays(1),
            'status' => 'completed',
        ]);
        Task::factory()->create([
            'due_date' => now()->addDays(1),
            'status' => 'pending',
        ]);
        Task::factory()->create([
            'due_date' => null,
            'status' => 'pending',
        ]);

        $overdueTasks = $this->repository->getOverdue();

        $this->assertCount(2, $overdueTasks);
        $overdueTasks->each(function ($task) {
            $this->assertNotEquals('completed', $task->status);
            $this->assertTrue($task->due_date->isPast());
        });
    }

    /** @test */
    public function it_can_mark_task_as_completed()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $completed = $this->repository->markAsCompleted($task->id);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_marking_non_existent_task_as_completed()
    {
        $this->expectException(TaskNotFoundException::class);
        $this->expectExceptionMessage('Task com ID 999 não encontrada');

        $this->repository->markAsCompleted(999);
    }

    /** @test */
    public function it_returns_fresh_instance_after_update()
    {
        $task = Task::factory()->create(['title' => 'Original']);

        $updated = $this->repository->update($task->id, ['title' => 'Updated']);

        $this->assertEquals('Updated', $updated->title);
        $this->assertNotSame($task, $updated);
    }

    /** @test */
    public function it_returns_fresh_instance_after_mark_as_completed()
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $completed = $this->repository->markAsCompleted($task->id);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotSame($task, $completed);
    }
}

