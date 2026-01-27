<?php

namespace Sowailem\FieldGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array applyReadSecurity(\Illuminate\Database\Eloquent\Model $model, $user = null)
 * @method static array applyWriteSecurity(\Illuminate\Database\Eloquent\Model $model, $user = null)
 * @method static array| \Illuminate\Support\Collection apply(\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection $data, $user = null)
 * @method static array filterWriteAttributes(string|\Illuminate\Database\Eloquent\Model $model, array $input, $user = null)
 * @method static void enableAutomaticEnforcement()
 * 
 * @see \Sowailem\FieldGuard\FieldGuard
 */
class FieldGuard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fieldguard';
    }
}
