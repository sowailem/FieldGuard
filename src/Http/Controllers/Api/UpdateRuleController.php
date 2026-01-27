<?php

namespace Sowailem\FieldGuard\Http\Controllers\Api;

use Sowailem\FieldGuard\Http\Requests\UpdateFieldGuardRuleRequest;
use Sowailem\FieldGuard\Http\Resources\FieldGuardRuleResource;
use Sowailem\FieldGuard\Services\FieldGuardRuleService;

class UpdateRuleController
{
    public function __invoke(UpdateFieldGuardRuleRequest $request, int $id, FieldGuardRuleService $service)
    {
        $rule = $service->getRule($id);
        $updatedRule = $service->updateRule($rule, $request->validated());

        return new FieldGuardRuleResource($updatedRule);
    }
}
