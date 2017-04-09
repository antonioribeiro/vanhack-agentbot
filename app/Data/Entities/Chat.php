<?php

namespace App\Data\Entities;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_id',
        'channel',
        'user',
        'chat_type_id',
        'user_id',
        'activation_key',
    ];

    public function getIsActivatedAttribute()
    {
        return ! is_null($this->activated_at);
    }
}
