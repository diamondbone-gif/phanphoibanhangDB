<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditObserver
{
    private const HIDDEN = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function created(Model $model): void
    {
        $this->write($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), self::HIDDEN);
        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        $this->write($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write($model, 'deleted', $model->getAttributes(), []);
    }

    private function write(Model $model, string $event, array $old, array $new): void
    {
        $request = app()->runningInConsole() ? null : request();
        $reason = $request?->input('reason')
            ?? $request?->input('return_reason')
            ?? $request?->input('cancel_reason')
            ?? $request?->input('note');

        AuditLog::query()->create([
            'actor_id' => auth('admin')->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'route_name' => $request?->route()?->getName(),
            'request_method' => $request?->method(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'reason' => is_scalar($reason) ? (string) $reason : null,
            'old_values' => $this->safeValues($old),
            'new_values' => $this->safeValues($new),
        ]);
    }

    private function safeValues(array $values): array
    {
        return Arr::except($values, self::HIDDEN);
    }
}
