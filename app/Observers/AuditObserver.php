<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->audit($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->audit($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->audit($model, 'deleted', $model->getOriginal(), null);
    }

    private function audit(Model $model, string $action, ?array $old, ?array $new): void
    {
        app(AuditService::class)->log(class_basename($model), $action, $model, $old, $new);
    }
}
