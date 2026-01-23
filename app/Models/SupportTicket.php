<?php

namespace App\Models;

use App\Enum\Support\Category;
use App\Enum\Support\Priority;
use App\Enum\Support\Status;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_id',
        'assigned_to',
        'subject',
        'category',
        'priority',
        'status',
    ];

    /**
     * Casts (VERY IMPORTANT for enums)
     */
    protected $casts = [
        'category' => Category::class,
        'priority' => Priority::class,
        'status'   => Status::class,
    ];


    /* ===================== RELATIONSHIPS ===================== */

    /**
     * Ticket owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Assigned manager/admin
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Ticket messages (chat)
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'support_ticket_id');
    }

    /* ===================== SCOPES ===================== */

    public function scopeOpen($query)
    {
        return $query->where('status', Status::OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', Status::CLOSED);
    }

    public function scopeByCategory($query, Category $category)
    {
        return $query->where('category', $category->value);
    }

    /* ===================== HELPERS ===================== */

    public function isOpen(): bool
    {
        return $this->status === Status::OPEN;
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }
}
