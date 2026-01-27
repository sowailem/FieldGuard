<?php

namespace Sowailem\FieldGuard\Http\Controllers\Api;

use Sowailem\FieldGuard\Http\Requests\CreateFieldGuardRuleRequest;
use Sowailem\FieldGuard\Http\Resources\FieldGuardRuleResource;
use Sowailem\FieldGuard\Services\FieldGuardRuleService;

class CreateRuleController
{
    public function __invoke(CreateFieldGuardRuleRequest $request, FieldGuardRuleService $service)
    {
        $rule = $service->createRule($request->validated());

        return new FieldGuardRuleResource($rule);
    }
}
