<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'color',
    ];

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_tag');
    }
}
