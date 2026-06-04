<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['name', 'type', 'created_by_id'];

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot('last_read_message_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function displayName(User $forUser): string
    {
        if ($this->isGroup()) {
            return $this->name ?? 'Qrup';
        }
        $other = $this->users->firstWhere('id', '!=', $forUser->id);
        return $other?->name ?? 'Silinmiş istifadəçi';
    }
}
