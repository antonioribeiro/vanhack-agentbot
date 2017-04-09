<?php

namespace App\Data\Entities;

class ChatMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text',
        'chat_id',
        'message_id',
        'type',
    ];

    public function getIsActivatedAttribute()
    {
        return ! is_null($this->activated_at);
    }
}
