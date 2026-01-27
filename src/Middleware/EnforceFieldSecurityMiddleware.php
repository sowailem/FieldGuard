<?php

namespace Sowailem\FieldGuard\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sowailem\FieldGuard\Facades\FieldGuard;

class EnforceFieldSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     * @param string ...$modelClasses
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$modelClasses): mixed
    {
        if (!empty($modelClasses) && $request->isMethodSafe() === false) {
            $user = $request->user();
            $data = $request->all();

            foreach ($modelClasses as $modelClass) {
                if (class_exists($modelClass)) {
                    $data = FieldGuard::filterWriteAttributes($modelClass, $data, $user);
                }
            }
            
            $request->replace($data);
        }

        return $next($request);
    }
}
