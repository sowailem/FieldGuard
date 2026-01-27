<?php

namespace Sowailem\FieldGuard\Database\Factories;

use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldGuardRuleFactory extends Factory
{
    protected $model = FieldGuardRule::class;

    public function definition()
    {
        return [
            'model_class' => 'App\Models\User',
            'field_name' => $this->faker->word,
            'read_policy' => null,
            'write_policy' => null,
            'mask' => null,
            'is_active' => true,
        ];
    }
}
