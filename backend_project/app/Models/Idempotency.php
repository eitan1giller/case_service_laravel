<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idempotency extends Model
{
    public $table = 'idempotency';
    public $incrementing = false;
    protected $primaryKey = 'key';
    public $timestamps = false;

    protected $casts = [
        'response_payload' => 'array',
    ];

    protected $fillable = [
        'key',
        'tracking_id',
        'response_payload',
    ];
}
