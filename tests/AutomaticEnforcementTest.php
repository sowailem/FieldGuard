<?php

namespace Sowailem\FieldGuard\Tests;

use Orchestra\Testbench\TestCase;
use Sowailem\FieldGuard\FieldGuardServiceProvider;
use Sowailem\FieldGuard\Facades\FieldGuard;
use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class AutomaticEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [FieldGuardServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('fieldguard.automatic_enforcement', true);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        $this->app['db']->connection()->getSchemaBuilder()->create('auto_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('secret')->nullable();
            $table->integer('salary')->nullable();
        });
    }

    public function test_it_automatically_filters_read_on_retrieval()
    {
        FieldGuardRule::create([
            'model_class' => AutoTestModel::class,
            'field_name' => 'secret',
            'read_policy' => 'false',
            'is_active' => true,
        ]);

        $model = AutoTestModel::create(['name' => 'Test', 'secret' => 'top-secret']);
        
        // Clear internal state if any, though retrieved event happens on fetch
        $retrieved = AutoTestModel::find($model->id);

        $this->assertTrue($retrieved->isHidden('secret'));
    }

    public function test_it_automatically_masks_read_on_retrieval()
    {
        FieldGuardRule::create([
            'model_class' => AutoTestModel::class,
            'field_name' => 'secret',
            'read_policy' => 'false',
            'mask' => 'MASKED',
            'is_active' => true,
        ]);

        $model = AutoTestModel::create(['name' => 'Test', 'secret' => 'top-secret']);
        $retrieved = AutoTestModel::find($model->id);

        $this->assertEquals('MASKED', $retrieved->secret);
    }

    public function test_it_automatically_prevents_unauthorized_write_on_save()
    {
        FieldGuardRule::create([
            'model_class' => AutoTestModel::class,
            'field_name' => 'salary',
            'write_policy' => 'false',
            'is_active' => true,
        ]);

        $model = AutoTestModel::create(['name' => 'Test', 'salary' => 1000]);
        
        $model->salary = 5000;
        $model->save();

        $this->assertEquals(1000, $model->fresh()->salary);
    }
}

class AutoTestModel extends Model
{
    protected $table = 'auto_test_models';
    protected $guarded = [];
    public $timestamps = false;
}
