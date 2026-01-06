<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'file_side_1_path',
        'file_side_2_path',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'document_type'    => 'string',
        'file_side_1_path' => 'string',
        'file_side_2_path' => 'string',
        'rejection_reason' => 'string',
        'reviewed_by'      => 'integer',
        'reviewed_at'      => 'datetime'
    ];

    /**
     * Relationship: KYC Document belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
