<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = ['po_item_id'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
