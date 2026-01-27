<?php

namespace Sowailem\FieldGuard\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Sowailem\FieldGuard\Http\Resources\FieldGuardRuleResource;
use Sowailem\FieldGuard\Services\FieldGuardRuleService;

class ListRuleController
{
    public function __invoke(Request $request, FieldGuardRuleService $service)
    {
        Gate::authorize('manage-field-guard');

        $filters = $request->only(['model_class', 'field_name', 'is_active']);
        $perPage = $request->get('per_page', 15);

        $rules = $service->listRules($filters, $perPage);

        return FieldGuardRuleResource::collection($rules);
    }
}
