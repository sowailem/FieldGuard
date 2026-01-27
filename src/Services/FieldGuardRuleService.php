<?php

namespace Sowailem\FieldGuard\Services;

use Sowailem\FieldGuard\Models\FieldGuardRule;
use Sowailem\FieldGuard\Repositories\FieldGuardRuleRepository;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FieldGuardRuleService
{
    protected FieldGuardRuleRepository $repository;

    public function __construct(FieldGuardRuleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List rules with optional filtering and pagination.
     */
    public function listRules(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FieldGuardRule::query();

        if (!empty($filters['model_class'])) {
            $query->where('model_class', $filters['model_class']);
        }

        if (!empty($filters['field_name'])) {
            $query->where('field_name', 'like', '%' . $filters['field_name'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new rule.
     */
    public function createRule(array $data): FieldGuardRule
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing rule.
     */
    public function updateRule(FieldGuardRule $rule, array $data): FieldGuardRule
    {
        return $this->repository->update($rule, $data);
    }

    /**
     * Delete a rule.
     */
    public function deleteRule(FieldGuardRule $rule): void
    {
        $this->repository->delete($rule);
    }

    /**
     * Get a specific rule.
     */
    public function getRule(int $id): FieldGuardRule
    {
        return FieldGuardRule::findOrFail($id);
    }
}
