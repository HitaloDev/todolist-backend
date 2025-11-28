<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_tasks_with_pagination()
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                            'completed_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals(15, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_list_tasks_with_custom_per_page()
    {
        Task::factory()->count(20)->create();

        $response = $this->getJson('/api/tasks?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_a_task()
    {
        $taskData = [
            'title' => 'Nova Task',
            'description' => 'Descrição da task',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task criada com sucesso',
                'data' => [
                    'title' => 'Nova Task',
                    'description' => 'Descrição da task',
                    'status' => 'pending',
                    'priority' => 'high',
                ],
            ]);

        $this->assertDatabaseHas('tasks', ['title' => 'Nova Task']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_a_task()
    {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_status_enum_when_creating_a_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task Test',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_priority_enum_when_creating_a_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task Test',
            'priority' => 'invalid_priority',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function it_validates_due_date_is_not_in_the_past()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task Test',
            'due_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function it_can_show_a_specific_task()
    {
        $task = Task::factory()->create([
            'title' => 'Task Específica',
        ]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => 'Task Específica',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_task_not_found()
    {
        $response = $this->getJson('/api/tasks/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task não encontrada',
            ]);
    }

    /** @test */
    public function it_can_update_a_task()
    {
        $task = Task::factory()->create([
            'title' => 'Título Original',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Título Atualizado',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task atualizada com sucesso',
                'data' => [
                    'title' => 'Título Atualizado',
                    'status' => 'in_progress',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Atualizado',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_can_delete_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deletada com sucesso',
            ]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_can_mark_task_as_completed()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task marcada como completa',
                'data' => [
                    'status' => 'completed',
                ],
            ]);

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    /** @test */
    public function it_can_filter_tasks_by_status()
    {
        Task::factory()->count(3)->create(['status' => 'pending']);
        Task::factory()->count(2)->create(['status' => 'completed']);

        $response = $this->getJson('/api/tasks/status/pending');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_returns_error_for_invalid_status_filter()
    {
        $response = $this->getJson('/api/tasks/status/invalid');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_filter_tasks_by_priority()
    {
        Task::factory()->count(2)->create(['priority' => 'high']);
        Task::factory()->count(3)->create(['priority' => 'low']);

        $response = $this->getJson('/api/tasks/priority/high');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_returns_error_for_invalid_priority_filter()
    {
        $response = $this->getJson('/api/tasks/priority/invalid');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
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

        $response = $this->getJson('/api/tasks/overdue');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_statistics()
    {
        // Limpa tasks existentes
        Task::query()->forceDelete();
        
        // Cria tasks com status específico
        Task::factory()->count(3)->create(['status' => 'pending', 'priority' => 'high']);
        Task::factory()->count(2)->create(['status' => 'in_progress', 'priority' => 'medium']);
        Task::factory()->count(5)->create(['status' => 'completed', 'priority' => 'low']);

        $response = $this->getJson('/api/tasks/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 10,
                    'pending' => 3,
                    'in_progress' => 2,
                    'completed' => 5,
                ],
            ])
            ->assertJsonPath('data.by_priority.high', 3)
            ->assertJsonPath('data.by_priority.medium', 2)
            ->assertJsonPath('data.by_priority.low', 5);
    }

    /** @test */
    public function it_updates_completed_at_when_status_changes_to_completed()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    /** @test */
    public function it_clears_completed_at_when_status_changes_from_completed()
    {
        $task = Task::factory()->create([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'pending',
        ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals('pending', $task->status);
        $this->assertNull($task->completed_at);
    }
}

