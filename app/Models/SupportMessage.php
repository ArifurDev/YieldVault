<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{

    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'message',
        'attachment',
    ];

    /**
     * Message sender (user / manager / admin)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Parent support ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }
}
