<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    protected $fillable = [
        'tenant_id',
        'keyword',
        'reply',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * FAQ / Keyword Auto-Reply — visitor ke message me se pehla matching
     * keyword rule dhundo (case-insensitive substring match).
     */
    public static function findMatch(int $tenantId, string $messageBody): ?self
    {
        try {
            return self::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get()
                ->first(function ($rule) use ($messageBody) {
                    return $rule->keyword !== '' && stripos($messageBody, $rule->keyword) !== false;
                });
        } catch (\Throwable $e) {
            // Migration abhi tak nahi chali ho to bhi chat na tootey.
            return null;
        }
    }
}
