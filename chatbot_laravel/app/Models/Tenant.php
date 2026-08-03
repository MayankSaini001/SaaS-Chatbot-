<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'email',
        'slug',
        'is_active',
        'plan',
        'trial_ends_at',

        'stripe_id',
        'stripe_status',
        'stripe_price_id',
        'subscription_ends_at',
    ];

    public function widgets()
    {
        return $this->hasMany(Widget::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}