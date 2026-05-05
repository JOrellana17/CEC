<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->clean($request->except(['password', 'password_confirmation', 'current_password'])));

        return $next($request);
    }

    private function clean(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->clean($value);
                continue;
            }

            if (is_string($value)) {
                $input[$key] = trim(strip_tags($value));
            }
        }

        return $input;
    }
}
