<?php

namespace Sowailem\FieldGuard\Repositories;

use Sowailem\FieldGuard\Models\FieldGuardRule;
use Illuminate\Support\Facades\Cache;

class FieldGuardRuleRepository
{
    public function getAllActive()
    {
        $cacheKey = config('fieldguard.cache_tag', 'field_guard_rules');
        return Cache::rememberForever($cacheKey, function () {
            return FieldGuardRule::active()->get();
        });
    }

    public function getForModel(string $modelClass)
    {
        return $this->getAllActive()->where('model_class', $modelClass);
    }

    public function create(array $data)
    {
        $rule = FieldGuardRule::create($data);
        $this->clearCache();
        return $rule;
    }

    public function update(FieldGuardRule $rule, array $data)
    {
        $rule->update($data);
        $this->clearCache();
        return $rule;
    }

    public function delete(FieldGuardRule $rule)
    {
        $rule->delete();
        $this->clearCache();
    }

    public function clearCache()
    {
        $cacheKey = config('fieldguard.cache_tag', 'field_guard_rules');
        Cache::forget($cacheKey);
    }
}
