<?php

namespace Sowailem\FieldGuard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sowailem\FieldGuard\Database\Factories\FieldGuardRuleFactory;

class FieldGuardRule extends Model
{
    use HasFactory;

    protected $table = 'field_guard_rules';

    protected $fillable = [
        'model_class',
        'field_name',
        'read_policy',
        'write_policy',
        'mask',
        'is_active',
    ];

    protected $casts = [
        'read_policy' => 'array',
        'write_policy' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory()
    {
        return FieldGuardRuleFactory::new();
    }
}
