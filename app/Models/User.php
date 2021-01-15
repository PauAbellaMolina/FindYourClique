<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_id',
        'group_id',
        'username',
        'first_name',
        'last_name',
        'spotify_api_token',
        'interests_set',
        'interest_code_1',
        'interest_name_1',
        'interest_code_2',
        'interest_name_2',
        'interest_code_3',
        'interest_name_3',
        'interest_code_4',
        'interest_name_4',
        'interest_code_5',
        'interest_name_5',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];
}
