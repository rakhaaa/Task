### Step-by-Step Comprehensive Code

#### 1. Project Setup

- **Install Laravel and Initialize the Project:**
  ```sh
  composer global require laravel/installer
  laravel new task-manager
  cd task-manager
  ```

- **Configure Environment Variables:**
  Ensure the `.env` file is set up properly for your environment settings.

- **Run Initial Migrations:**
  ```sh
  php artisan migrate
  ```

#### 2. Define Migrations and Models

1. **Generate the Task Model and Migration:**
   ```sh
   php artisan make:model Task -m
   ```

2. **Define Task Migration:**
   Edit the migration file to define the `tasks` table structure:
   ```php
   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   class CreateTasksTable extends Migration
   {
       public function up()
       {
           Schema::create('tasks', function (Blueprint $table) {
               $table->uuid('id')->primary();
               $table->string('title');
               $table->text('description')->nullable();
               $table->boolean('completed')->default(false);
               $table->timestamps();
           });
       }

       public function down()
       {
           Schema::dropIfExists('tasks');
       }
   }
   ```

3. **Define Task Model:**
   ```php
   <?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Factories\HasFactory;
   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Concerns\HasUuids;

   class Task extends Model
   {
       use HasFactory, HasUuids;

       protected $keyType = 'string';
       public $incrementing = false;

       protected $fillable = [
           'title', 'description', 'completed',
       ];
   }
   ```

#### 3. Define Form Requests for Validation

1. **Generate TaskRequest:**
   ```sh
   php artisan make:request TaskRequest
   ```

2. **Define Validation Rules:**
   Edit the TaskRequest:
   ```php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class TaskRequest extends FormRequest
   {
       public function authorize()
       {
           return true;
       }

       public function rules()
       {
           return [
               'title' => 'required|string|max:255',
               'description' => 'nullable|string',
               'completed' => 'boolean',
           ];
       }
   }
   ```

#### 4. Implement Controllers

1. **Generate TaskController:**
   ```sh
   php artisan make:controller TaskController
   ```

2. **Implement CRUD Methods in TaskController:**
   Edit the TaskController to manage task data:
   ```php
   <?php

   namespace App\Http\Controllers;

   use App\Models\Task;
   use App\Http\Requests\TaskRequest;
   use App\Http\Resources\TaskResource;
   use Illuminate\Http\Request;

   class TaskController extends Controller
   {
       /**
        * Display a listing of the tasks.
        *
        * @return \Illuminate\Http\JsonResponse
        */
       public function index()
       {
           $tasks = Task::all();
           return response()->json(TaskResource::collection($tasks), 200);
       }

       /**
        * Store a newly created task in storage.
        *
        * @param  \App\Http\Requests\TaskRequest  $request
        * @return \Illuminate\Http\JsonResponse
        */
       public function store(TaskRequest $request)
       {
           $task = Task::create($request->validated());
           return response()->json(new TaskResource($task), 201);
       }

       /**
        * Display the specified task.
        *
        * @param  \App\Models\Task  $task
        * @return \Illuminate\Http\JsonResponse
        */
       public function show(Task $task)
       {
           return response()->json(new TaskResource($task), 200);
       }

       /**
        * Update the specified task in storage.
        *
        * @param  \App\Http\Requests\TaskRequest  $request
        * @param  \App\Models\Task  $task
        * @return \Illuminate\Http\JsonResponse
        */
       public function update(TaskRequest $request, Task $task)
       {
           $task->update($request->validated());
           return response()->json(new TaskResource($task), 200);
       }

       /**
        * Remove the specified task from storage.
        *
        * @param  \App\Models\Task  $task
        * @return \Illuminate\Http\JsonResponse
        */
       public function destroy(Task $task)
       {
           $task->delete();
           return response()->json(null, 204);
       }
   }
   ```

#### 5. Define API Routes

1. **Define Routes in `routes/web.php`:**
   ```php
   <?php

   use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\TaskController;

   Route::prefix('api')->group(function () {
       Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
       Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
       Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
       Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
       Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
   });
   ```

#### 6. Set Up Resources

1. **Generate TaskResource:**
   ```sh
   php artisan make:resource TaskResource
   ```

2. **Define TaskResource:**
   Edit the resource file:
   ```php
   <?php

   namespace App\Http\Resources;

   use Illuminate\Http\Resources\Json\JsonResource;

   class TaskResource extends JsonResource
   {
       /**
        * Transform the resource into an array.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return array
        */
       public function toArray($request)
       {
           return [
               'id' => $this->id,
               'title' => $this->title,
               'description' => $this->description,
               'completed' => $this->completed,
               'created_at' => $this->created_at,
               'updated_at' => $this->updated_at,
           ];
       }
   }
   ```

#### 7. Set Up Factories and Seeders

1. **Generate TaskFactory:**
   ```sh
   php artisan make:factory TaskFactory --model=Task
   ```

2. **Define Factory:**
   Edit the factory file:
   ```php
   <?php

   namespace Database\Factories;

   use App\Models\Task;
   use Illuminate\Database\Eloquent\Factories\Factory;
   use Illuminate\Support\Str;

   class TaskFactory extends Factory
   {
       protected $model = Task::class;

       public function definition()
       {
           return [
               'id' => (string) Str::uuid(),
               'title' => $this->faker->sentence,
               'description' => $this->faker->paragraph,
               'completed' => $this->faker->boolean,
           ];
       }
   }
   ```

#### 8. Comprehensive Testing

1. **Generate TaskTest:**
   ```sh
   php artisan make:test TaskTest
   ```

2. **Write Test Methods in TaskTest:**
   Edit TaskTest to verify CRUD operations:
   ```php
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

Absolutely, let's continue from where we left off:

```php
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
   ```

### Summary

1. **Project Setup**: Install Laravel, configure environment variables, and run initial migrations.
2. **Define Models and Migrations**: Create and define the Task model and migration.
3. **Form Requests for Validation**: Create and define validation rules in TaskRequest.
4. **Implement Controllers**: Generate TaskController and implement CRUD methods.
5. **API Routes**: Define routes for task management in `routes/web.php`.
6. **Resources**: Generate and define TaskResource for consistent JSON responses.
7. **Factories and Seeders**: Generate TaskFactory for creating test data.
8. **Comprehensive Testing**: Write tests in TaskTest to verify CRUD operations.