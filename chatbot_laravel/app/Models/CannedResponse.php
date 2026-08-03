<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CannedResponse extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'body',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
