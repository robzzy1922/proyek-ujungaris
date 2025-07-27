<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait, HasApiTokens;

    protected $table = 'admin';
    protected $fillable = [
        'namaAdmin',
        'nip',
        'email',
        'noHp',
        'password',
        'profile',
        'is_email_verified',
        'email_verification_code',
        'email_verification_expires_at',
        'email_verified_at',
        'verification_email'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'is_email_verified' => 'boolean',
    ];

    // Relation with Dokumen
    public function dokumens()
    {
        return $this->hasMany(Dokumen::class, 'id_admin');
    }
}
