<?php

namespace Sowailem\FieldGuard\Http\Controllers\Api;

use Illuminate\Support\Facades\Gate;
use Sowailem\FieldGuard\Http\Resources\FieldGuardRuleResource;
use Sowailem\FieldGuard\Services\FieldGuardRuleService;

class ViewRuleController
{
    public function __invoke(int $id, FieldGuardRuleService $service)
    {
        Gate::authorize('manage-field-guard');

        $rule = $service->getRule($id);

        return new FieldGuardRuleResource($rule);
    }
}
