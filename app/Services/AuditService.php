<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(string $module, string $action, ?Model $model = null, ?array $old = null, ?array $new = null, ?string $description = null): void
    {
        $request = request();

        AuditLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'record_id' => $model?->getKey(),
            'description' => $description ?? $this->description($module, $action, $model),
            'old_values' => $old ? $this->clean($old) : null,
            'new_values' => $new ? $this->clean($new) : null,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? $request->userAgent() : null,
        ]);
    }

    private function description(string $module, string $action, ?Model $model): string
    {
        $id = $model ? ' #'.$model->getKey() : '';

        return ucfirst($action).' '.$module.$id.'.';
    }

    private function clean(array $values): array
    {
        return Arr::except($values, ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at']);
    }
}
