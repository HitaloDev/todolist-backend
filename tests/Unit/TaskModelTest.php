<?php

namespace Tests\Unit;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'title',
            'description',
            'status',
            'priority',
            'due_date',
            'completed_at',
        ];

        $task = new Task();

        $this->assertEquals($fillable, $task->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $task = Task::factory()->create([
            'due_date' => '2025-12-31',
            'completed_at' => '2025-11-26 10:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->due_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->completed_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->updated_at);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $task = Task::factory()->create();

        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
        $this->assertNotNull($task->fresh()->deleted_at);
    }

    /** @test */
    public function pending_scope_filters_pending_tasks()
    {
        Task::factory()->count(3)->create(['status' => 'pending']);
        Task::factory()->count(2)->create(['status' => 'completed']);

        $pendingTasks = Task::pending()->get();

        $this->assertCount(3, $pendingTasks);
        $pendingTasks->each(function ($task) {
            $this->assertEquals('pending', $task->status);
        });
    }

    /** @test */
    public function in_progress_scope_filters_in_progress_tasks()
    {
        Task::factory()->count(2)->create(['status' => 'in_progress']);
        Task::factory()->count(3)->create(['status' => 'pending']);

        $inProgressTasks = Task::inProgress()->get();

        $this->assertCount(2, $inProgressTasks);
        $inProgressTasks->each(function ($task) {
            $this->assertEquals('in_progress', $task->status);
        });
    }

    /** @test */
    public function completed_scope_filters_completed_tasks()
    {
        Task::factory()->count(5)->create(['status' => 'completed']);
        Task::factory()->count(2)->create(['status' => 'pending']);

        $completedTasks = Task::completed()->get();

        $this->assertCount(5, $completedTasks);
        $completedTasks->each(function ($task) {
            $this->assertEquals('completed', $task->status);
        });
    }

    /** @test */
    public function by_priority_scope_filters_tasks_by_priority()
    {
        Task::factory()->count(4)->create(['priority' => 'high']);
        Task::factory()->count(2)->create(['priority' => 'low']);

        $highPriorityTasks = Task::byPriority('high')->get();

        $this->assertCount(4, $highPriorityTasks);
        $highPriorityTasks->each(function ($task) {
            $this->assertEquals('high', $task->priority);
        });
    }

    /** @test */
    public function is_completed_returns_true_when_task_is_completed()
    {
        $task = Task::factory()->create(['status' => 'completed']);

        $this->assertTrue($task->isCompleted());
    }

    /** @test */
    public function is_completed_returns_false_when_task_is_not_completed()
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $this->assertFalse($task->isCompleted());
    }

    /** @test */
    public function mark_as_completed_updates_status_and_completed_at()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $task->markAsCompleted();

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    /** @test */
    public function is_overdue_returns_true_when_due_date_is_past_and_not_completed()
    {
        $task = Task::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        $this->assertTrue($task->isOverdue());
    }

    /** @test */
    public function is_overdue_returns_false_when_due_date_is_future()
    {
        $task = Task::factory()->create([
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $this->assertFalse($task->isOverdue());
    }

    /** @test */
    public function is_overdue_returns_false_when_task_is_completed()
    {
        $task = Task::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => 'completed',
        ]);

        $this->assertFalse($task->isOverdue());
    }

    /** @test */
    public function is_overdue_returns_false_when_due_date_is_null()
    {
        $task = Task::factory()->create([
            'due_date' => null,
            'status' => 'pending',
        ]);

        $this->assertFalse($task->isOverdue());
    }

    /** @test */
    public function it_can_create_task_with_default_values()
    {
        $task = Task::factory()->create([
            'title' => 'Task with Defaults',
        ]);

        $this->assertNotNull($task->status);
        $this->assertNotNull($task->priority);
        $this->assertNull($task->completed_at);
    }

    /** @test */
    public function it_can_chain_scopes()
    {
        Task::factory()->create(['status' => 'pending', 'priority' => 'high']);
        Task::factory()->create(['status' => 'pending', 'priority' => 'low']);
        Task::factory()->create(['status' => 'completed', 'priority' => 'high']);

        $tasks = Task::pending()->byPriority('high')->get();

        $this->assertCount(1, $tasks);
        $this->assertEquals('pending', $tasks->first()->status);
        $this->assertEquals('high', $tasks->first()->priority);
    }
}

