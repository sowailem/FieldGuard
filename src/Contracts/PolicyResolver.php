<?php

namespace Sowailem\FieldGuard\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PolicyResolver
{
    /**
     * Resolve the given policy array for the model and user.
     */
    public function resolve(array $policy, Model $model, $user): bool;
}
