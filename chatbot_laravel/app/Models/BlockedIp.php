<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $fillable = [
        'tenant_id',
        'ip_address',
        'reason',
        'blocked_by',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public static function isBlocked(int $tenantId, ?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        // Agar migration abhi tak run nahi hui (blocked_ips table missing) ya
        // koi aur DB issue aaye, to chat ko kabhi mat todo — bas "not blocked"
        // maan lo. Blocking ek safety feature hai, iski wajah se normal chat
        // (start/send) fail nahi honi chahiye.
        try {
            return self::where('tenant_id', $tenantId)
                ->where('ip_address', $ip)
                ->exists();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('BlockedIp::isBlocked check failed (has the migration run?): ' . $e->getMessage());
            return false;
        }
    }
}
