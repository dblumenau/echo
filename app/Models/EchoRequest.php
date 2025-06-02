<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EchoRequest extends Model
{
    protected $fillable = [
        'method',
        'url',
        'headers',
        'query_params',
        'body',
        'ip_address',
        'user_agent',
        'response_status',
    ];

    protected $casts = [
        'headers' => 'array',
        'query_params' => 'array',
    ];
}
