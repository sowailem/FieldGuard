<?php

namespace Sowailem\FieldGuard\Tests;

use Orchestra\Testbench\TestCase;
use Sowailem\FieldGuard\FieldGuardServiceProvider;
use Sowailem\FieldGuard\Middleware\EnforceFieldSecurityMiddleware;
use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
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

    public function test_middleware_filters_request_data()
    {
        FieldGuardRule::create([
            'model_class' => MiddlewareTestModel::class,
            'field_name' => 'salary',
            'write_policy' => 'false',
            'is_active' => true,
        ]);

        $request = Request::create('/test', 'POST', [
            'name' => 'John',
            'salary' => 5000,
        ]);

        $middleware = new EnforceFieldSecurityMiddleware();

        $middleware->handle($request, function ($req) {
            $this->assertArrayHasKey('name', $req->all());
            $this->assertArrayNotHasKey('salary', $req->all());
        }, MiddlewareTestModel::class);
    }

    public function test_middleware_filters_request_data_with_multiple_models()
    {
        FieldGuardRule::create([
            'model_class' => MiddlewareTestModel::class,
            'field_name' => 'salary',
            'write_policy' => 'false',
            'is_active' => true,
        ]);

        FieldGuardRule::create([
            'model_class' => AnotherMiddlewareTestModel::class,
            'field_name' => 'secret_key',
            'write_policy' => 'false',
            'is_active' => true,
        ]);

        $request = Request::create('/test', 'POST', [
            'name' => 'John',
            'salary' => 5000,
            'secret_key' => 'shhh',
        ]);

        $middleware = new EnforceFieldSecurityMiddleware();

        $middleware->handle($request, function ($req) {
            $this->assertArrayHasKey('name', $req->all());
            $this->assertArrayNotHasKey('salary', $req->all());
            $this->assertArrayNotHasKey('secret_key', $req->all());
        }, MiddlewareTestModel::class, AnotherMiddlewareTestModel::class);
    }

    public function test_middleware_does_not_filter_safe_methods()
    {
        FieldGuardRule::create([
            'model_class' => MiddlewareTestModel::class,
            'field_name' => 'salary',
            'write_policy' => 'false',
            'is_active' => true,
        ]);

        $request = Request::create('/test', 'GET', [
            'name' => 'John',
            'salary' => 5000,
        ]);

        $middleware = new EnforceFieldSecurityMiddleware();

        $middleware->handle($request, function ($req) {
            $this->assertArrayHasKey('name', $req->all());
            $this->assertArrayHasKey('salary', $req->all());
        }, MiddlewareTestModel::class);
    }
}

class MiddlewareTestModel extends Model
{
    protected $table = 'test_models';
    protected $guarded = [];
}

class AnotherMiddlewareTestModel extends Model
{
    protected $table = 'another_test_models';
    protected $guarded = [];
}
