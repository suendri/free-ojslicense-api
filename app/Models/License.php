<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'license_key', 'domain', 'is_active', 'activated_at'
    ];
}
