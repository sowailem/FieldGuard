<?php

namespace Sowailem\FieldGuard\Tests;

use Orchestra\Testbench\TestCase;
use Sowailem\FieldGuard\FieldGuardServiceProvider;
use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $guarded = [];
}

class TestModel extends Model
{
    protected $guarded = [];
}

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [FieldGuardServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define the management gate
        Gate::define('manage-field-guard', function ($user = null) {
            return true; // For testing purposes
        });

        Gate::define('create-field-guard', function ($user = null) {
            return true;
        });

        Gate::define('update-field-guard', function ($user = null) {
            return true;
        });

        Gate::define('delete-field-guard', function ($user = null) {
            return true;
        });
    }

    public function test_can_list_rules()
    {
        FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
            'is_active' => true,
        ]);

        $response = $this->getJson('/field-guard/rules');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['field_name' => 'salary']);
    }

    public function test_can_create_rule()
    {
        $response = $this->postJson('/field-guard/rules', [
            'model_class' => TestModel::class,
            'field_name' => 'email',
            'read_policy' => ['roles' => ['admin']],
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('field_guard_rules', [
            'field_name' => 'email'
        ]);
    }

    public function test_validate_model_class_on_create()
    {
        $response = $this->postJson('/field-guard/rules', [
            'model_class' => 'NonExistentClass',
            'field_name' => 'email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['model_class']);
    }

    public function test_can_update_rule()
    {
        $rule = FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
            'is_active' => true,
        ]);

        $response = $this->putJson("/field-guard/rules/{$rule->id}", [
            'field_name' => 'new_salary_field',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('field_guard_rules', [
            'id' => $rule->id,
            'field_name' => 'new_salary_field'
        ]);
    }

    public function test_can_delete_rule()
    {
        $rule = FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/field-guard/rules/{$rule->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('field_guard_rules', [
            'id' => $rule->id
        ]);
    }

    public function test_unauthorized_access_is_blocked()
    {
        // Redefine gates to deny access
        Gate::define('manage-field-guard', fn() => false);
        Gate::define('create-field-guard', fn() => false);
        Gate::define('update-field-guard', fn() => false);
        Gate::define('delete-field-guard', fn() => false);

        $this->getJson('/field-guard/rules')->assertStatus(403);
        
        $this->postJson('/field-guard/rules', [
            'model_class' => TestModel::class,
            'field_name' => 'email',
        ])->assertStatus(403);

        $rule = FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
        ]);

        $this->getJson("/field-guard/rules/{$rule->id}")->assertStatus(403);
        $this->putJson("/field-guard/rules/{$rule->id}", ['field_name' => 'new'])->assertStatus(403);
        $this->deleteJson("/field-guard/rules/{$rule->id}")->assertStatus(403);
    }

    public function test_granular_abilities_work()
    {
        // Allow ONLY create
        Gate::define('manage-field-guard', fn() => false);
        Gate::define('create-field-guard', fn() => true);
        Gate::define('update-field-guard', fn() => false);
        Gate::define('delete-field-guard', fn() => false);

        $this->postJson('/field-guard/rules', [
            'model_class' => TestModel::class,
            'field_name' => 'email',
        ])->assertStatus(201);

        $this->getJson('/field-guard/rules')->assertStatus(403);
    }
}
