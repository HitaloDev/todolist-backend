<?php

namespace Tests\Unit;

use App\Exceptions\InvalidTaskStatusException;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    protected TaskService $service;
    protected TaskRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepositoryInterface::class);
        $this->service = new TaskService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_all_tasks_with_pagination()
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->repository->shouldReceive('paginate')
            ->once()
            ->with(15)
            ->andReturn($paginator);

        $result = $this->service->getAllTasks(15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_task_by_id()
    {
        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($task);

        $result = $this->service->getTaskById(1);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_can_create_task()
    {
        $taskData = [
            'title' => 'Nova Task',
            'description' => 'Descrição',
        ];

        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('create')
            ->once()
            ->with($taskData)
            ->andReturn($task);

        $result = $this->service->createTask($taskData);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_can_update_task()
    {
        $taskData = [
            'title' => 'Título Atualizado',
        ];

        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('update')
            ->once()
            ->with(1, $taskData)
            ->andReturn($task);

        $result = $this->service->updateTask(1, $taskData);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_adds_completed_at_when_updating_status_to_completed()
    {
        $taskData = [
            'status' => 'completed',
        ];

        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('update')
            ->once()
            ->withArgs(function ($id, $data) {
                return $id === 1 
                    && $data['status'] === 'completed' 
                    && isset($data['completed_at']);
            })
            ->andReturn($task);

        $result = $this->service->updateTask(1, $taskData);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_clears_completed_at_when_updating_status_from_completed()
    {
        $taskData = [
            'status' => 'pending',
        ];

        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('update')
            ->once()
            ->withArgs(function ($id, $data) {
                return $id === 1 
                    && $data['status'] === 'pending' 
                    && $data['completed_at'] === null;
            })
            ->andReturn($task);

        $result = $this->service->updateTask(1, $taskData);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_can_delete_task()
    {
        $this->repository->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->deleteTask(1);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_tasks_by_status()
    {
        $tasks = new Collection([
            Mockery::mock(Task::class),
            Mockery::mock(Task::class),
        ]);

        $this->repository->shouldReceive('findByStatus')
            ->once()
            ->with('pending')
            ->andReturn($tasks);

        $result = $this->service->getTasksByStatus('pending');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_status()
    {
        $this->expectException(InvalidTaskStatusException::class);
        $this->expectExceptionMessage("Status 'invalid' é inválido");

        $this->service->getTasksByStatus('invalid');
    }

    /** @test */
    public function it_can_get_tasks_by_priority()
    {
        $tasks = new Collection([
            Mockery::mock(Task::class),
        ]);

        $this->repository->shouldReceive('findByPriority')
            ->once()
            ->with('high')
            ->andReturn($tasks);

        $result = $this->service->getTasksByPriority('high');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_priority()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Prioridade inválida: invalid");

        $this->service->getTasksByPriority('invalid');
    }

    /** @test */
    public function it_can_get_overdue_tasks()
    {
        $tasks = new Collection([
            Mockery::mock(Task::class),
            Mockery::mock(Task::class),
        ]);

        $this->repository->shouldReceive('getOverdue')
            ->once()
            ->andReturn($tasks);

        $result = $this->service->getOverdueTasks();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_can_complete_task()
    {
        $task = Mockery::mock(Task::class);

        $this->repository->shouldReceive('markAsCompleted')
            ->once()
            ->with(1)
            ->andReturn($task);

        $result = $this->service->completeTask(1);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        $allTasks = new Collection(array_fill(0, 10, Mockery::mock(Task::class)));
        $pendingTasks = new Collection(array_fill(0, 3, Mockery::mock(Task::class)));
        $inProgressTasks = new Collection(array_fill(0, 2, Mockery::mock(Task::class)));
        $completedTasks = new Collection(array_fill(0, 5, Mockery::mock(Task::class)));
        $overdueTasks = new Collection(array_fill(0, 1, Mockery::mock(Task::class)));
        $lowPriorityTasks = new Collection(array_fill(0, 2, Mockery::mock(Task::class)));
        $mediumPriorityTasks = new Collection(array_fill(0, 4, Mockery::mock(Task::class)));
        $highPriorityTasks = new Collection(array_fill(0, 4, Mockery::mock(Task::class)));

        $this->repository->shouldReceive('all')->once()->andReturn($allTasks);
        $this->repository->shouldReceive('findByStatus')->with('pending')->once()->andReturn($pendingTasks);
        $this->repository->shouldReceive('findByStatus')->with('in_progress')->once()->andReturn($inProgressTasks);
        $this->repository->shouldReceive('findByStatus')->with('completed')->once()->andReturn($completedTasks);
        $this->repository->shouldReceive('getOverdue')->once()->andReturn($overdueTasks);
        $this->repository->shouldReceive('findByPriority')->with('low')->once()->andReturn($lowPriorityTasks);
        $this->repository->shouldReceive('findByPriority')->with('medium')->once()->andReturn($mediumPriorityTasks);
        $this->repository->shouldReceive('findByPriority')->with('high')->once()->andReturn($highPriorityTasks);

        $result = $this->service->getStatistics();

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(3, $result['pending']);
        $this->assertEquals(2, $result['in_progress']);
        $this->assertEquals(5, $result['completed']);
        $this->assertEquals(1, $result['overdue']);
        $this->assertArrayHasKey('by_priority', $result);
        $this->assertEquals(2, $result['by_priority']['low']);
        $this->assertEquals(4, $result['by_priority']['medium']);
        $this->assertEquals(4, $result['by_priority']['high']);
    }
}

