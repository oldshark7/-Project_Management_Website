<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        // Map request parameter strings to database role values
        $mappedRoles = array_map(function($role) {
            $roleLower = strtolower(trim($role));
            if ($roleLower === 'project_manager') {
                return ['project manager', 'project_manager'];
            }
            if ($roleLower === 'pmo') {
                return ['pmo', 'project management officer'];
            }
            return [$roleLower];
        }, $roles);

        // Flatten the mapped roles array
        $allowedRoles = [];
        foreach ($mappedRoles as $subArray) {
            $allowedRoles = array_merge($allowedRoles, $subArray);
        }

        if (in_array(strtolower($userRole), $allowedRoles)) {
            return $next($request);
        }

        // Return a 403 Forbidden response if user doesn't have required role
        abort(403, 'Unauthorized action. Anda tidak memiliki akses ke halaman ini.');
    }
}
