<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'is_online',
        'last_seen',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime', 
            'is_online' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'agent_id');
    }
	
	public function isOnline()
{
    return $this->last_seen && $this->last_seen->gt(now()->subMinutes(2));
}

    /**
     * Agent Presence & Status — "online" mana jaata hai jab manual status
     * "online" ho AND recently active bhi ho (last_seen). Away/Busy set
     * karne par visitor ko "agents online" nahi dikhega, chahe agent tab
     * khula rakhe.
     */
    public function isAvailable(): bool
    {
        return ($this->status ?? 'online') === 'online' && $this->isOnline();
    }

    /**
     * Widget ke liye "agents online" flag — status column ke saath aur
     * uske bina (migration na chalne ki soorat me) dono jagah kaam kare.
     */
    public static function anyAvailable(int $tenantId): bool
    {
        $base = self::where('tenant_id', $tenantId)
            ->whereNotIn('role', ['admin', 'viewer'])
            ->where('last_seen', '>=', now()->subMinutes(3));

        try {
            return (clone $base)->where('status', 'online')->exists();
        } catch (\Throwable $e) {
            // 'status' column shayad migration na chalne ki wajah se
            // maujood nahi — purane behaviour par fallback karo taaki
            // chat kabhi break na ho.
            return $base->exists();
        }
    }
}
