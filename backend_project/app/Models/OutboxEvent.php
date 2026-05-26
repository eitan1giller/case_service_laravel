<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboxEvent extends Model
{
    use HasFactory;

    protected $table = 'outbox_events';

    protected $casts = [
        'payload' => 'array',
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $fillable = [
        'aggregate_type',
        'aggregate_id',
        'event_type',
        'payload',
        'published',
        'publish_attempts',
        'last_error',
        'published_at',
    ];
}
