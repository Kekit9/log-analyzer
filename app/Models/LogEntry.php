<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_ip',
        'date',
        'http_info',
        'error_code',
        'response_size',
        'referer_ip',
        'referer_ip_host',
        'user_agent',
    ];
}
