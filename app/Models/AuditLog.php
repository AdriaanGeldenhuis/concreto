<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_user_id',
        'action',
        'entity',
        'entity_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function log(string $action, string $entity, ?int $entityId = null, ?array $meta = null): static
    {
        return static::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'meta' => $meta,
        ]);
    }
}
