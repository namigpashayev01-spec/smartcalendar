<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    public $timestamps    = false;
    protected $fillable   = ['conversation_id', 'user_id', 'last_read_message_id'];
    const CREATED_AT      = 'created_at';
}
