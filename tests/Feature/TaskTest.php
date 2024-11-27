<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_get_all_tasks()
    {
        Task::factory()->count(5)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'completed',
                'created_at',
                'updated_at',
            ]
        ]);
    }

    public function test_can_create_task()
    {
        $taskData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'completed' => false,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201);
        $response->assertJsonFragment($taskData);
    }

    public function test_can_get_single_task()
    {
        $task = Task::factory()->create();

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'completed' => $task->completed,
            'created_at' => $task->created_at->toJson(),
            'updated_at' => $task->updated_at->toJson(),
        ]);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create();

        $updatedTaskData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated Task Description',
            'completed' => true,
        ];

        $response = $this->putJson('/api/tasks/' . $task->id, $updatedTaskData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updatedTaskData);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(204);
    }
}
