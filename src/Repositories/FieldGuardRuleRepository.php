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
        $this->validateRuleData($data);
        $rule = FieldGuardRule::create($data);
        $this->clearCache();
        return $rule;
    }

    protected function validateRuleData(array $data)
    {
        $modelClass = $data['model_class'] ?? null;
        $fieldName = $data['field_name'] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class '{$modelClass}' does not exist.");
        }

        $model = new $modelClass;
        if (!($model instanceof \Illuminate\Database\Eloquent\Model)) {
            throw new \InvalidArgumentException("Class '{$modelClass}' is not an Eloquent model.");
        }

        // Check if field exists in fillable, guarded, or as a database column if we can
        // For now, checking if it is in fillable or guarded might be enough or if we can get schema
        $fillable = $model->getFillable();
        $guarded = $model->getGuarded();
        
        // This is a basic check. Some fields might not be in fillable/guarded but exist in DB.
        // However, usually fields we want to guard are either fillable or present in the model.
        // A better way is to check the table schema if the model is connected.
        
        // To be more robust, we can try to see if it's a valid attribute or column.
        // But for many cases, checking fillable/guarded/hidden or if it's the primary key is a good start.
        
        // Another way is checking Schema:
        try {
            $table = $model->getTable();
            $schema = $model->getConnection()->getSchemaBuilder();
            if (!$schema->hasColumn($table, $fieldName)) {
                // If not a column, maybe it's a dynamic property? 
                // But the requirement says "field of it is excist".
                throw new \InvalidArgumentException("Field '{$fieldName}' does not exist on model '{$modelClass}'.");
            }
        } catch (\Exception $e) {
            // Fallback if DB connection fails or other issues
            if (!in_array($fieldName, $fillable) && !in_array($fieldName, $guarded) && $fieldName !== $model->getKeyName()) {
                 throw new \InvalidArgumentException("Field '{$fieldName}' does not exist on model '{$modelClass}'.");
            }
        }
    }

    public function update(FieldGuardRule $rule, array $data)
    {
        $this->validateRuleData(array_merge($rule->toArray(), $data));
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
