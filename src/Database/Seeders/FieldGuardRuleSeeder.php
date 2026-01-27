<?php

namespace Sowailem\FieldGuard\Database\Seeders;

use Illuminate\Database\Seeder;
use Sowailem\FieldGuard\Repositories\FieldGuardRuleRepository;

class FieldGuardRuleSeeder extends Seeder
{
    public function run(FieldGuardRuleRepository $repository)
    {
        // Example: Secure email for a User model
        $repository->create([
            'model_class' => 'App\Models\User',
            'field_name' => 'email',
            'read_policy' => ['roles' => ['admin', 'hr'], 'allow_self' => true],
            'write_policy' => ['roles' => ['admin']],
            'is_active' => true,
        ]);

        // Example: Mask password for a User model
        $repository->create([
            'model_class' => 'App\Models\User',
            'field_name' => 'password',
            'read_policy' => 'false',
            'mask' => '*********',
            'is_active' => true,
        ]);
    }
}
