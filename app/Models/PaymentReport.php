<?php

namespace App\Models;

use App\Enum\Payment\Category;
use App\Enum\Payment\IssueType;
use App\Enum\Payment\ReportStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'manager_id',
        'transaction_id',
        'category',
        'issue_type',
        'message',
        'status',
        'created_by',
        'resolved_at',
    ];

    protected $casts = [
        'category'   => Category::class,
        'issue_type' => IssueType::class,
        'status'     => ReportStatus::class,
        'resolved_at'=> 'datetime',
    ];

    /* ================= Relations ================= */

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function transaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
