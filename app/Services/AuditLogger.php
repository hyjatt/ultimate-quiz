<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogger
{
    public function __construct(private Request $request) {}

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(string $action, ?Model $subject = null, ?array $before = null, ?array $after = null): AuditLog
    {
        $sensitive = ['password', 'remember_token'];

        return AuditLog::create([
            'actor_id' => $this->request->user()?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'before' => $before === null ? null : Arr::except($before, $sensitive),
            'after' => $after === null ? null : Arr::except($after, $sensitive),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}
