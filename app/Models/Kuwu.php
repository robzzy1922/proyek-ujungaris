<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Kuwu extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use HasApiTokens;

    protected $table = 'kuwu';
    protected $guard = 'kuwu';

    protected $fillable = [
        'nama_kuwu',
        'nip',
        'email',
        'password',
        'no_hp',
        'prodi',
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
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'is_email_verified' => 'boolean',
    ];

    // Define relationships if needed
}
