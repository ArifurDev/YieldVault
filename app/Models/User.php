<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,softDeletes,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'avatar',
        'email',
        'password',
        'role',
        'address',
        'bio',
        'provider',
        'provider_id',
        'is_notify',
        'kyc_status',
        'ref_code',
        'ref_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_notify' => 'boolean',
            'role' => Role::class,
        ];
    }

//    protected static function booted()
//    {
//        static::creating(function ($user) {
//            // Generate uuid only if it's not already set
//            if (empty($user->uuid)) {
//                $lastCode = self::max('uuid') ?? 100000;
//                $user->uuid = $lastCode + 1;
//            }
//        });
//    }


    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    /* ========================= Relationships ========================= */
    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseTokens::class);
    }


    public function invitedUsers()
    {
        return $this->hasMany(User::class, 'ref_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'from_user_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'to_user_id');
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }

}
