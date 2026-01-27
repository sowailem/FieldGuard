<?php

namespace Sowailem\FieldGuard\Tests;

use Orchestra\Testbench\TestCase;
use Sowailem\FieldGuard\FieldGuardServiceProvider;
use Sowailem\FieldGuard\Facades\FieldGuard;
use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class FieldGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [FieldGuardServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'FieldGuard' => FieldGuard::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function test_it_filters_read_attributes_via_database_rules()
    {
        FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
            'read_policy' => 'view-salary',
            'is_active' => true,
        ]);

        $user = new TestUser(['id' => 1]);
        $model = new TestModel([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'salary' => 5000,
        ]);

        // Mock Gate
        Gate::define('view-salary', function ($u) {
            return false;
        });

        $secured = FieldGuard::apply($model, $user);

        $this->assertArrayHasKey('name', $secured);
        $this->assertArrayHasKey('email', $secured);
        $this->assertArrayNotHasKey('salary', $secured);
    }

    public function test_it_masks_attributes()
    {
        FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'ssn',
            'read_policy' => 'false',
            'mask' => '***-***-***',
            'is_active' => true,
        ]);

        $user = new TestUser(['id' => 1]);
        $model = new TestModel([
            'name' => 'John Doe',
            'ssn' => '123-456-789',
        ]);

        $secured = FieldGuard::apply($model, $user);

        $this->assertEquals('***-***-***', $secured['ssn']);
    }

    public function test_it_filters_write_attributes()
    {
        FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'salary',
            'write_policy' => 'update-salary',
            'is_active' => true,
        ]);

        $user = new TestUser(['id' => 1]);
        $model = new TestModel(['id' => 10]);

        $input = [
            'name' => 'Updated Name',
            'salary' => 9000,
        ];

        // Mock Gate to allow name but deny salary update
        Gate::define('update-salary', function ($u) {
            return false;
        });

        $filtered = FieldGuard::filterWriteAttributes($model, $input, $user);

        $this->assertArrayHasKey('name', $filtered);
        $this->assertArrayNotHasKey('salary', $filtered);
    }

    public function test_it_caches_database_rules()
    {
        FieldGuardRule::create([
            'model_class' => TestModel::class,
            'field_name' => 'secret',
            'read_policy' => 'false',
            'is_active' => true,
        ]);

        $cacheTag = config('fieldguard.cache_tag', 'field_guard_rules');

        // First call to populate cache
        FieldGuard::apply(new TestModel(), new TestUser());
        
        $this->assertTrue(Cache::has($cacheTag));

        // Delete from DB manually
        FieldGuardRule::query()->delete();

        // Should still be filtered because of cache
        $secured = FieldGuard::apply(new TestModel(['secret' => 'val']), new TestUser());
        $this->assertArrayNotHasKey('secret', $secured);

        // Clear cache
        app(\Sowailem\FieldGuard\Repositories\FieldGuardRuleRepository::class)->clearCache();
        
        $secured = FieldGuard::apply(new TestModel(['secret' => 'val']), new TestUser());
        $this->assertArrayHasKey('secret', $secured);
    }
}

class TestUser extends Model
{
    protected $guarded = [];
}

class TestModel extends Model
{
    protected $guarded = [];
    protected $fillable = ['name', 'email', 'salary', 'ssn', 'secret'];
}
