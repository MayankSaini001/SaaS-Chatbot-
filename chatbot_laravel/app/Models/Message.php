<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'attachment',
        'is_read',
        'is_edited',
        'is_deleted',
        'edited_at',
    ];

    protected $casts = [
        'is_edited'  => 'boolean',
        'is_deleted' => 'boolean',
        'edited_at'  => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}