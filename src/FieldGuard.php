<?php

namespace Sowailem\FieldGuard;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Sowailem\FieldGuard\Repositories\FieldGuardRuleRepository;

class FieldGuard
{
    protected mixed $ruleRepository;

    public function __construct(FieldGuardRuleRepository $ruleRepository = null)
    {
        $this->ruleRepository = $ruleRepository ?? app(FieldGuardRuleRepository::class);
    }

    /**
     * Register global observers to automatically enforce field security.
     */
    public function enableAutomaticEnforcement(): void
    {
        // Automatic Read Enforcement
        Event::listen('eloquent.retrieved: *', function ($eventName, array $data) {
            $model = $data[0];
            $this->applyReadSecurity($model);
        });

        // Automatic Write Enforcement
        Event::listen('eloquent.saving: *', function ($eventName, array $data) {
            $model = $data[0];
            $this->applyWriteSecurity($model);
        });
    }

    /**
     * Apply read security to a model by hiding or masking unauthorized fields.
     */
    public function applyReadSecurity(Model $model, $user = null): void
    {
        $user = $user ?? auth()->user();
        $rules = $this->getRulesForModel(get_class($model));

        foreach ($rules as $rule) {
            $field = $rule['field'];
            $permission = $rule['read'];

            if ($permission && !$this->checkPermission($permission, $model, $user)) {
                if ($rule['mask'] !== null) {
                    $model->setAttribute($field, $rule['mask']);
                } else {
                    $model->makeHidden($field);
                }
            }
        }
    }

    /**
     * Apply to write security to a model by removing unauthorized fields before saving.
     */
    public function applyWriteSecurity(Model $model, $user = null): void
    {
        $user = $user ?? auth()->user();
        $rules = $this->getRulesForModel(get_class($model));

        foreach ($rules as $rule) {
            $field = $rule['field'];
            $permission = $rule['write'];

            if ($model->isDirty($field) && $permission && !$this->checkPermission($permission, $model, $user)) {
                // Revert the attribute to its original value if not authorized
                $model->setAttribute($field, $model->getOriginal($field));
            }
        }
    }

    /**
     * Apply read security to a model or collection of models and return as array.
     */
    public function apply(Model|Collection $data, $user = null): array|Collection
    {
        if ($data instanceof Collection) {
            return $data->map(fn($model) => $this->secureModel($model, $user, 'read'));
        }

        return $this->secureModel($data, $user, 'read');
    }

    /**
     * Secure a single model's attributes.
     */
    public function secureModel(Model $model, $user, string $action): array
    {
        $attributes = $model->toArray();
        $rules = $this->getRulesForModel(get_class($model));

        foreach ($rules as $rule) {
            $field = $rule['field'];
            $permission = ($action === 'read') ? $rule['read'] : $rule['write'];

            if ($permission && !$this->checkPermission($permission, $model, $user)) {
                if ($action === 'read') {
                    if ($rule['mask'] !== null) {
                        $attributes[$field] = $rule['mask'];
                    } else {
                        unset($attributes[$field]);
                    }
                } else {
                    unset($attributes[$field]);
                }
            }
        }

        return $attributes;
    }

    /**
     * Check if the user has permission for a specific rule.
     */
    protected function checkPermission($permission, Model $model, $user): bool
    {
        if (is_callable($permission)) {
            return $permission($user, $model);
        }

        if (is_array($permission)) {
            return $this->resolveArrayPolicy($permission, $model, $user);
        }

        if (is_string($permission)) {
            if ($permission === 'false') {
                return false;
            }

            if ($permission === 'true') {
                return true;
            }

            // Handle 'role:admin' or 'self' or a Gate/Policy name
            if (str_starts_with($permission, 'role:')) {
                $role = substr($permission, 5);
                return $user && method_exists($user, 'hasRole') && $user->hasRole($role);
            }

            if ($permission === 'self') {
                return $user && $user->id === $model->getAttribute($model->getKeyName());
            }

            return Gate::forUser($user)->allows($permission, $model);
        }

        return true;
    }

    /**
     * Resolve a complex policy defined as an array (from DB JSON).
     */
    protected function resolveArrayPolicy(array $policy, Model $model, $user): bool
    {
        // Check for custom resolver in config
        $resolverClass = config('fieldguard.resolver');
        if ($resolverClass && class_exists($resolverClass)) {
            try {
                return app($resolverClass)->resolve($policy, $model, $user);
            } catch (BindingResolutionException|CircularDependencyException $e) {
                
            }
        }

        // Default resolution logic
        $allowed = true;

        if (isset($policy['roles'])) {
            $hasRole = false;
            foreach ($policy['roles'] as $role) {
                if ($user && method_exists($user, 'hasRole') && $user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }
            $allowed = $allowed && $hasRole;
        }

        if (isset($policy['allow_self']) && $policy['allow_self']) {
            $isSelf = $user && $user->id === $model->getAttribute($model->getKeyName());
            if (isset($policy['roles'])) {
                 $allowed = $allowed || $isSelf;
            } else {
                 $allowed = $allowed && $isSelf;
            }
        }
        
        if (isset($policy['gate'])) {
            $allowed = $allowed && Gate::forUser($user)->allows($policy['gate'], $model);
        }

        return $allowed;
    }

    /**
     * Get security rules for the given model from database.
     *
     * @param string $modelClass
     * @return array
     */
    protected function getRulesForModel(string $modelClass): array
    {
        try {
            $dbRules = $this->ruleRepository->getForModel($modelClass);

            return $dbRules->map(function ($dbRule) {
                return [
                    'field' => $dbRule->field_name,
                    'read' => $dbRule->read_policy,
                    'write' => $dbRule->write_policy,
                    'mask' => $dbRule->mask,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            // Fail safely if DB is not available or table doesn't exist
            return [];
        }
    }

    /**
     * Filter unauthorized fields from input data for creation/update.
     */
    public function filterWriteAttributes(string|Model $model, array $input, $user = null): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);
        
        $rules = $this->getRulesForModel($modelClass);

        if (empty($rules)) {
            return $input;
        }

        $modelInstance = is_string($model) ? new $model : $model;

        foreach ($rules as $rule) {
            if (array_key_exists($rule['field'], $input)) {
                $permission = $rule['write'];
                if ($permission && !$this->checkPermission($permission, $modelInstance, $user)) {
                    unset($input[$rule['field']]);
                }
            }
        }

        return $input;
    }
}
