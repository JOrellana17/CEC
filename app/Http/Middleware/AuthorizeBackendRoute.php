<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeBackendRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName) {
            return $next($request);
        }

        $permission = $this->permissionFor($routeName);

        if ($permission && ! $user->hasPermission($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }

    private function permissionFor(string $routeName): ?string
    {
        if ($routeName === 'backend.dashboard') {
            return 'dashboard.view';
        }

        if (in_array($routeName, ['backend.users.profile', 'backend.users.update_profile', 'backend.users.update_avatar'], true)) {
            return null;
        }

        if (str_starts_with($routeName, 'backend.audit.')) {
            return 'audit.view';
        }

        $resource = explode('.', str_replace('backend.', '', $routeName))[0] ?? null;

        return match (true) {
            $resource === 'reports' && str_contains($routeName, 'export') => 'reports.export',
            $resource === 'reports' => 'reports.view',
            $resource === 'settings' && ! str_ends_with($routeName, 'index') => 'settings.update',
            $resource === 'settings' => 'settings.view',
            in_array($resource, ['bookings', 'reservations', 'rooms', 'guests', 'invoices', 'payments', 'services', 'users', 'roles', 'permissions', 'floors', 'room-types'], true) => $this->resourcePermission($resource, $routeName),
            $resource === 'icalendar' => 'reservations.calendar',
            default => null,
        };
    }

    private function resourcePermission(string $resource, string $routeName): string
    {
        $module = str_replace('-', '_', $resource);

        return match (true) {
            str_ends_with($routeName, '.create'), str_ends_with($routeName, '.store') => "{$module}.create",
            str_contains($routeName, '.edit'), str_contains($routeName, '.update'), str_contains($routeName, 'toggle'), str_contains($routeName, 'assign'), str_contains($routeName, 'revoke') => "{$module}.edit",
            str_ends_with($routeName, '.destroy') => "{$module}.delete",
            str_contains($routeName, 'check_in') => "{$module}.checkin",
            str_contains($routeName, 'check_out') => "{$module}.checkout",
            str_contains($routeName, 'update_status') => "{$module}.update_status",
            str_contains($routeName, 'update_cleaning_status') => "{$module}.update_cleaning_status",
            str_contains($routeName, '.cancel') => "{$module}.cancel",
            str_contains($routeName, 'refund') => "{$module}.refund",
            str_contains($routeName, 'mark_paid'), str_contains($routeName, 'pdf'), str_contains($routeName, 'email') => "{$module}.edit",
            default => "{$module}.view",
        };
    }
}
