<?php

namespace Sowailem\FieldGuard\Database\Seeders;

use Illuminate\Database\Seeder;
use Sowailem\FieldGuard\Models\FieldGuardRule;

class FieldGuardRuleSeeder extends Seeder
{
    public function run()
    {
        // Example: Secure salary for User model
        FieldGuardRule::create([
            'model_class' => 'App\Models\User',
            'field_name' => 'salary',
            'read_policy' => ['roles' => ['admin', 'hr'], 'allow_self' => true],
            'write_policy' => ['roles' => ['admin']],
            'is_active' => true,
        ]);

        // Example: Mask SSN for User model
        FieldGuardRule::create([
            'model_class' => 'App\Models\User',
            'field_name' => 'ssn',
            'read_policy' => 'false',
            'mask' => '***-***-***',
            'is_active' => true,
        ]);
    }
}
