<?php

use Illuminate\Support\Facades\Route;
use Sowailem\FieldGuard\Http\Controllers\Api\ListRuleController;
use Sowailem\FieldGuard\Http\Controllers\Api\CreateRuleController;
use Sowailem\FieldGuard\Http\Controllers\Api\ViewRuleController;
use Sowailem\FieldGuard\Http\Controllers\Api\UpdateRuleController;
use Sowailem\FieldGuard\Http\Controllers\Api\DeleteRuleController;

Route::prefix(config('fieldguard.api.prefix', 'field-guard'))
    ->middleware(config('fieldguard.api.middleware', ['api', 'auth:sanctum']))
    ->group(function () {
        Route::get('rules', ListRuleController::class)->name('field-guard.rules.list');
        Route::post('rules', CreateRuleController::class)->name('field-guard.rules.create');
        Route::get('rules/{id}', ViewRuleController::class)->name('field-guard.rules.view');
        Route::put('rules/{id}', UpdateRuleController::class)->name('field-guard.rules.update');
        Route::patch('rules/{id}', UpdateRuleController::class);
        Route::delete('rules/{id}', DeleteRuleController::class)->name('field-guard.rules.delete');
    });
