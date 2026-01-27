<?php

namespace Sowailem\FieldGuard\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Sowailem\FieldGuard\Services\FieldGuardRuleService;

class DeleteRuleController
{
    public function __invoke(int $id, FieldGuardRuleService $service)
    {
        Gate::authorize('any', [['manage-field-guard', 'delete-field-guard']]);

        $rule = $service->getRule($id);
        $service->deleteRule($rule);

        return response()->noContent();
    }
}
